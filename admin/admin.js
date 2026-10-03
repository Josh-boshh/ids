(function () {
  var authView = document.getElementById('auth-view');
  var authForm = document.getElementById('auth-form');
  var caseForm = document.getElementById('case-form');
  var caseList = document.getElementById('case-list');
  var authError = document.getElementById('auth-error');
  var saveStatus = document.getElementById('save-status');
  var casePreviewLink = document.getElementById('preview-link');
  var siteSeoForm = document.getElementById('site-seo-form');
  var siteSeoStatus = document.getElementById('site-seo-status');
  var cases = [];
  var csrfToken = '';
  var activeId = '';
  var slugEdited = false;
  var timer;

  function field(name) {
    return caseForm.elements.namedItem(name);
  }

  function siteField(name) {
    return siteSeoForm.elements.namedItem(name);
  }

  function setStatus(message, isError) {
    saveStatus.textContent = message;
    saveStatus.dataset.error = isError ? 'true' : 'false';
  }

  function setSiteSeoStatus(message, isError) {
    siteSeoStatus.textContent = message;
    siteSeoStatus.dataset.error = isError ? 'true' : 'false';
  }

  function setSection(section) {
    var showSeo = section === 'seo';
    document.getElementById('admin-home').hidden = true;
    document.getElementById('workspace').hidden = false;
    document.getElementById('case-collection').hidden = showSeo;
    document.getElementById('case-editor').hidden = showSeo;
    document.getElementById('site-seo-editor').hidden = !showSeo;
    document.getElementById('cases-nav-button').classList.toggle('is-active', !showSeo);
    document.getElementById('site-seo-nav-button').classList.toggle('is-active', showSeo);
    document.getElementById('cases-nav-button').setAttribute('aria-pressed', String(!showSeo));
    document.getElementById('site-seo-nav-button').setAttribute('aria-pressed', String(showSeo));
  }

  function updateSiteSeoPreview() {
    var title = siteField('seoTitle').value.trim() || 'Your site title appears here';
    var description = siteField('metaDescription').value.trim() || 'Your site description appears here.';
    document.getElementById('site-preview-title').textContent = title;
    document.getElementById('site-preview-description').textContent = description;
    document.getElementById('site-preview-url').textContent = siteField('canonicalUrl').value.trim() || 'idsintegrated.com';
    document.getElementById('site-seo-title-count').textContent = siteField('seoTitle').value.length;
    document.getElementById('site-meta-description-count').textContent = siteField('metaDescription').value.length;
  }

  function fillSiteSeo(settings) {
    ['siteName', 'seoTitle', 'metaDescription', 'canonicalUrl', 'ogTitle', 'ogDescription', 'ogImage'].forEach(function (name) {
      siteField(name).value = settings[name] || '';
    });
    setSiteSeoStatus('', false);
    updateSiteSeoPreview();
  }

  function setAuthMode() {
    authView.hidden = false;
    document.getElementById('admin-home').hidden = true;
    document.getElementById('workspace').hidden = true;
    document.getElementById('logout-button').hidden = true;
    authError.textContent = '';
    document.getElementById('auth-title').textContent = 'Sign in to continue';
    document.getElementById('auth-message').textContent = 'Sign in with your admin password to load and manage the case studies in the database.';
    document.getElementById('auth-password').autocomplete = 'current-password';
    authForm.dataset.action = 'login';
  }

  function api(action, options) {
    options = options || {};
    var headers = { 'Accept': 'application/json' };
    if (options.body) headers['Content-Type'] = 'application/json';
    if (csrfToken && options.auth !== false) headers['X-CSRF-Token'] = csrfToken;
    return fetch('api.php?action=' + encodeURIComponent(action), {
      method: options.method || 'GET',
      credentials: 'same-origin',
      headers: headers,
      body: options.body ? JSON.stringify(options.body) : undefined
    }).then(function (response) {
      return response.json().catch(function () { return {}; }).then(function (data) {
        if (!response.ok) {
          var error = new Error(data.error || 'The request could not be completed.');
          error.status = response.status;
          throw error;
        }
        return data;
      });
    });
  }

  function openWorkspace(token) {
    csrfToken = token || '';
    authView.hidden = true;
    document.getElementById('admin-home').hidden = false;
    document.getElementById('workspace').hidden = true;
    document.getElementById('logout-button').hidden = false;
    return api('admin-list').then(function (data) {
      cases = Array.isArray(data.cases) ? data.cases : [];
      renderList();
      if (cases.length) selectCase(cases[0].id);
      else newCase();
      return api('admin-seo').then(function (seoData) {
        fillSiteSeo(seoData.settings);
      }).catch(function (error) {
        setSiteSeoStatus(error.message, true);
      });
    }).catch(function (error) {
      if (error.status === 401 || error.status === 403) {
        csrfToken = '';
        setAuthMode();
        return;
      }
      setStatus(error.message, true);
    });
  }

  function initialize() {
    api('auth-status', { auth: false }).then(function (status) {
      if (status.authenticated) return openWorkspace(status.csrfToken);
      setAuthMode();
    }).catch(function (error) {
      setAuthMode();
      authError.textContent = error.message;
    });
  }

  function renderList() {
    var query = document.getElementById('case-search').value.trim().toLowerCase();
    caseList.replaceChildren();
    var filtered = cases.filter(function (item) {
      return (item.title + ' ' + item.category).toLowerCase().indexOf(query) !== -1;
    });
    document.getElementById('case-count').textContent = String(cases.length).padStart(2, '0') + ' entries';
    document.getElementById('empty-list').hidden = filtered.length !== 0;
    filtered.forEach(function (item) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'case-list-item';
      button.setAttribute('aria-current', item.id === activeId ? 'true' : 'false');
      var cover = document.createElement('img');
      cover.className = 'case-list-cover';
      cover.src = /^https:\/\//i.test(item.image) ? item.image : '../' + item.image.replace(/^\//, '');
      cover.alt = '';
      cover.loading = 'lazy';
      var title = document.createElement('strong');
      title.textContent = item.title;
      var info = document.createElement('span');
      info.textContent = item.category;
      var status = document.createElement('small');
      status.className = item.published ? 'status-published' : 'status-draft';
      status.textContent = item.published ? 'Published' : 'Draft';
      if (item.featured) {
        var mark = document.createElement('em');
        mark.textContent = 'Featured';
        info.appendChild(mark);
      }
      var text = document.createElement('span');
      text.className = 'case-list-copy';
      text.append(title, info, status);
      button.append(cover, text);
      button.addEventListener('click', function () { selectCase(item.id); });
      caseList.appendChild(button);
    });
  }

  function updatePreview() {
    var slug = field('slug').value.trim();
    var staticPages = {
      'federal-ministry-of-defence': 'case-federal-ministry-of-defence.html',
      'apc-promise-kept': 'case-apc-promise-kept.html',
      placom: 'case-placom.html'
    };
    casePreviewLink.href = slug && staticPages[slug] ? '../' + staticPages[slug] : '#';
    casePreviewLink.hidden = !activeId || !staticPages[slug];
  }

  function fillForm(item) {
    caseForm.reset();
    field('id').value = item ? item.id : '';
    ['slug', 'title', 'category', 'summary', 'image', 'imageAlt', 'content'].forEach(function (name) {
      field(name).value = item ? (item[name] || '') : '';
    });
    field('published').checked = item ? !!item.published : false;
    field('featured').checked = item ? !!item.featured : false;
    slugEdited = !!item;
    document.getElementById('editor-title').textContent = item ? item.title : 'New case study';
    document.getElementById('save-label').textContent = item ? 'Save changes' : 'Create case study';
    document.getElementById('delete-button').hidden = !item;
    casePreviewLink.hidden = !item;
    setStatus('', false);
    updatePreview();
  }

  function selectCase(id) {
    var selected = cases.find(function (item) { return item.id === id; });
    if (!selected) return;
    activeId = id;
    fillForm(selected);
    renderList();
  }

  function newCase() {
    activeId = '';
    fillForm(null);
    renderList();
    field('title').focus();
  }

  function slugify(value) {
    return value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 80);
  }

  document.getElementById('new-case-button').addEventListener('click', newCase);
  field('featured').addEventListener('change', function () {
    if (!field('featured').checked) return;
    cases.forEach(function (item) {
      if (item.id !== activeId) item.featured = false;
    });
    renderList();
  });
  document.getElementById('open-cases-button').addEventListener('click', function () { setSection('cases'); });
  document.getElementById('open-site-seo-button').addEventListener('click', function () { setSection('seo'); });
  document.getElementById('admin-home-nav-button').addEventListener('click', function () {
    document.getElementById('workspace').hidden = true;
    document.getElementById('admin-home').hidden = false;
  });
  document.getElementById('cases-nav-button').addEventListener('click', function () { setSection('cases'); });
  document.getElementById('site-seo-nav-button').addEventListener('click', function () { setSection('seo'); });
  document.getElementById('case-search').addEventListener('input', renderList);
  field('slug').addEventListener('input', function () { slugEdited = true; updatePreview(); });
  field('title').addEventListener('input', function () {
    if (!slugEdited) field('slug').value = slugify(field('title').value);
    updatePreview();
  });
  field('image').addEventListener('input', function () {
    renderList();
  });

  ['seoTitle', 'metaDescription', 'canonicalUrl', 'ogTitle', 'ogDescription'].forEach(function (name) {
    siteField(name).addEventListener('input', updateSiteSeoPreview);
  });

  siteSeoForm.addEventListener('submit', function (event) {
    event.preventDefault();
    var payload = {};
    new FormData(siteSeoForm).forEach(function (value, key) { payload[key] = String(value).trim(); });
    setSiteSeoStatus('Saving…', false);
    api('save-site-seo', { method: 'POST', body: payload }).then(function (result) {
      fillSiteSeo(result.settings);
      setSiteSeoStatus('Site SEO saved.', false);
      showToast('Site SEO saved.');
    }).catch(function (error) {
      setSiteSeoStatus(error.message, true);
    });
  });

  authForm.addEventListener('submit', function (event) {
    event.preventDefault();
    authError.textContent = '';
    var password = document.getElementById('auth-password').value;
    api('login', {
      method: 'POST',
      auth: false,
      body: { password: password }
    }).then(function (result) {
      document.getElementById('auth-password').value = '';
      openWorkspace(result.csrfToken).then(function () {
        showToast('Signed in.');
      });
    }).catch(function (error) {
      authError.textContent = error.message;
    });
  });

  document.getElementById('logout-button').addEventListener('click', function () {
    api('logout', { method: 'POST' }).then(function () {
      csrfToken = '';
      cases = [];
      caseForm.reset();
      initialize();
      showToast('Signed out.');
    }).catch(function (error) { setStatus(error.message, true); });
  });

  caseForm.addEventListener('submit', function (event) {
    event.preventDefault();
    var formData = new FormData(caseForm);
    var payload = {};
    formData.forEach(function (value, key) { payload[key] = String(value).trim(); });
    payload.published = field('published').checked;
    payload.featured = field('featured').checked;
    setStatus('Saving…', false);
    api('save', { method: 'POST', body: payload }).then(function (result) {
      var saved = result.case;
      var existing = cases.findIndex(function (item) { return item.id === saved.id; });
      var isNewCase = existing === -1;
      if (saved.featured) {
        cases.forEach(function (item) {
          if (item.id !== saved.id) item.featured = false;
        });
      }
      if (existing === -1) cases.push(saved);
      else cases[existing] = saved;
      activeId = saved.id;
      fillForm(saved);
      renderList();
      var successMessage = isNewCase
        ? (saved.published ? 'Case study published.' : 'Draft saved.')
        : (saved.published ? 'Changes saved.' : 'Draft updated.');
      setStatus(successMessage, false);
      showToast(successMessage);
    }).catch(function (error) { setStatus(error.message, true); });
  });

  document.getElementById('delete-button').addEventListener('click', function () {
    var selected = cases.find(function (item) { return item.id === activeId; });
    if (!selected || !window.confirm('Delete “' + selected.title + '”? This cannot be undone.')) return;
    api('delete', { method: 'POST', body: { id: selected.id } }).then(function () {
      cases = cases.filter(function (item) { return item.id !== selected.id; });
      activeId = '';
      renderList();
      if (cases.length) selectCase(cases[0].id);
      else newCase();
      showToast('Case study deleted.');
    }).catch(function (error) { setStatus(error.message, true); });
  });

  function showToast(message) {
    var toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('is-visible');
    window.clearTimeout(timer);
    timer = window.setTimeout(function () { toast.classList.remove('is-visible'); }, 2600);
  }

  initialize();
})();
