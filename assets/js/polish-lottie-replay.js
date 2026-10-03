(function () {
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var els = Array.prototype.slice.call(document.querySelectorAll('[data-animation-type="lottie"]'));
  if (!els.length) return;

  function findAnim(el) {
    if (!window.lottie || !lottie.getRegisteredAnimations) return null;
    var anims = lottie.getRegisteredAnimations();
    for (var i = 0; i < anims.length; i++) {
      var wrapper = anims[i].wrapper;
      if (wrapper && (wrapper === el || el.contains(wrapper))) return anims[i];
    }
    return null;
  }

  function attach(el) {
    var anim = findAnim(el);
    if (!anim) return false;

    if (!('IntersectionObserver' in window)) return true;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          anim.goToAndPlay(0, true);
        } else {
          anim.pause();
        }
      });
    }, { threshold: 0.25 });
    io.observe(el);
    return true;
  }

  // Webflow loads each Lottie JSON asynchronously, so the animation
  // instance may not be registered yet on page load. Poll briefly
  // until each one appears, then wire it up.
  var pending = els.slice();
  var tries = 0;
  var maxTries = 25; // ~7.5s at 300ms
  var poll = setInterval(function () {
    tries++;
    pending = pending.filter(function (el) { return !attach(el); });
    if (!pending.length || tries >= maxTries) clearInterval(poll);
  }, 300);
})();
