(function () {
  var revealScript = document.currentScript;
  var onHomepage = document.querySelector('.case-grid');
  var casePath = window.location.pathname.split('/').pop();
  var isCasePage = /^case-(federal-ministry-of-defence|apc-promise-kept|placom)\.html$/.test(casePath);
  if (revealScript && (onHomepage || isCasePage)) {
    var dataScript = document.createElement('script');
    dataScript.src = new URL('public-case-sync.js?v=1', revealScript.src).href;
    dataScript.defer = true;
    document.head.appendChild(dataScript);
  }

  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var els = document.querySelectorAll('.reveal-up');
  if (!els.length || !('IntersectionObserver' in window)) {
    els.forEach(function (el) { el.classList.add('is-visible'); });
    return;
  }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      entry.target.classList.toggle('is-visible', entry.isIntersecting);
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
  els.forEach(function (el) { io.observe(el); });
})();
