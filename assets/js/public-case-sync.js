(function () {
  var caseFiles = {
    'federal-ministry-of-defence': 'case-federal-ministry-of-defence.html',
    'apc-promise-kept': 'case-apc-promise-kept.html',
    placom: 'case-placom.html'
  };
  var pageToSlug = {};
  Object.keys(caseFiles).forEach(function (slug) {
    pageToSlug[caseFiles[slug]] = slug;
  });

  function setMeta(selector, value) {
    if (!value) return;
    var element = document.querySelector(selector);
    if (element) element.setAttribute('content', value);
  }

  function setSocialImage(attribute, key, path, scriptUrl) {
    var image = document.querySelector('meta[' + attribute + '="' + key + '"]');
    if (!path) {
      if (image) image.remove();
      return;
    }
    if (!image) {
      image = document.createElement('meta');
      image.setAttribute(attribute, key);
      document.head.appendChild(image);
    }
    image.setAttribute('content', resolveImage(path, scriptUrl));
  }

  function updateSiteSeo(settings, scriptUrl) {
    document.title = settings.seoTitle;
    setMeta('meta[name="description"]', settings.metaDescription);
    setMeta('meta[property="og:title"]', settings.ogTitle || settings.seoTitle);
    setMeta('meta[property="og:description"]', settings.ogDescription || settings.metaDescription);
    setMeta('meta[name="twitter:title"]', settings.twitterTitle || settings.ogTitle || settings.seoTitle);
    setMeta('meta[name="twitter:description"]', settings.twitterDescription || settings.ogDescription || settings.metaDescription);
    setSocialImage('property', 'og:image', settings.ogImage, scriptUrl);
    setSocialImage('name', 'twitter:image', settings.twitterImage || settings.ogImage, scriptUrl);

    var canonical = document.querySelector('link[rel="canonical"]');
    if (settings.canonicalUrl) {
      if (!canonical) {
        canonical = document.createElement('link');
        canonical.rel = 'canonical';
        document.head.appendChild(canonical);
      }
      canonical.href = settings.canonicalUrl;
    } else if (canonical) {
      canonical.remove();
    }

    document.querySelectorAll('script[type="application/ld+json"]').forEach(function (script) {
      try {
        var graph = JSON.parse(script.textContent);
        var organizations = Array.isArray(graph['@graph']) ? graph['@graph'] : [graph];
        organizations.forEach(function (entry) {
          var types = Array.isArray(entry['@type']) ? entry['@type'] : [entry['@type']];
          if (types.indexOf('Organization') !== -1) entry.name = settings.siteName;
        });
        script.textContent = JSON.stringify(graph);
      } catch (error) {
        return;
      }
    });
  }

  function resolveImage(path, scriptUrl) {
    if (/^https?:\/\//i.test(path)) return path;
    return new URL('../../' + path.replace(/^\/+/, ''), scriptUrl).href;
  }

  function updateCard(card, item, scriptUrl) {
    var title = card.querySelector('.case-feature-title, .case-grid-title');
    var summary = card.querySelector('.case-feature-desc, .case-grid-desc');
    var category = card.querySelector('.case-feature-tag, .case-grid-tag');
    var image = card.querySelector('.case-feature-media img, .case-grid-media img');

    if (title) title.textContent = item.title;
    if (summary) summary.textContent = item.summary;
    if (category) category.textContent = item.category;
    if (image) {
      image.src = resolveImage(item.image, scriptUrl);
      image.alt = item.imageAlt || '';
    }
    card.setAttribute('aria-label', item.title + ' case study – view case study');
    card.hidden = !item.published;
  }

  function updateDetail(item) {
    var heading = document.querySelector('main h1.h1');
    if (heading) {
      heading.textContent = item.title;
      var section = heading.closest('.section');
      var category = section && section.querySelector('.small-text.bold-text');
      var summary = section && section.querySelector('p.mb-0');
      if (category) category.textContent = item.category;
      if (summary) summary.textContent = item.summary;
    }
  }

  var scriptUrl = document.currentScript && document.currentScript.src
    ? document.currentScript.src
    : new URL('public-case-sync.js', window.location.href).href;
  fetch(new URL('../../admin/api.php?action=public-list', scriptUrl).href, { credentials: 'same-origin' })
    .then(function (response) {
      if (!response.ok) throw new Error('Published case studies are unavailable.');
      return response.json();
    })
    .then(function (data) {
      var cases = Array.isArray(data.cases) ? data.cases : [];
      var bySlug = {};
      cases.forEach(function (item) { bySlug[item.slug] = item; });

      document.querySelectorAll('.case-grid a.case-feature, .case-grid a.case-grid-card').forEach(function (card) {
        var file = (card.getAttribute('href') || '').split('/').pop().split('?')[0];
        var slug = pageToSlug[file];
        if (slug && bySlug[slug]) updateCard(card, bySlug[slug], scriptUrl);
        else if (slug) card.hidden = true;
      });

      var detailSlug = pageToSlug[window.location.pathname.split('/').pop()];
      if (detailSlug && bySlug[detailSlug]) updateDetail(bySlug[detailSlug]);

      if (document.querySelector('.case-grid')) {
        fetch(new URL('../../admin/api.php?action=public-seo', scriptUrl).href, { credentials: 'same-origin' })
          .then(function (response) {
            if (!response.ok) throw new Error('Site SEO settings are unavailable.');
            return response.json();
          })
          .then(function (seo) { updateSiteSeo(seo.settings, scriptUrl); })
          .catch(function () {});
      }
    })
    .catch(function () {});
})();
