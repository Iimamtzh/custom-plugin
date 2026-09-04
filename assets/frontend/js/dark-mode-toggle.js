(function () {
  'use strict';

  function getTheme() {
    var html = document.documentElement;
    return html.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
  }

  function setTheme(theme) {
    var html = document.documentElement;
    html.setAttribute('data-bs-theme', theme);
    // cookie 1 tahun, path /
    var expires = new Date();
    expires.setFullYear(expires.getFullYear() + 1);
    document.cookie = 'color_scheme=' + theme + '; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';
    syncButtons(theme);
    // event untuk hook lain
    document.dispatchEvent(new CustomEvent('cpDarkModeChanged', { detail: { theme: theme } }));
  }

  function syncButtons(theme) {
    var pressed = theme === 'dark' ? 'true' : 'false';
    document.querySelectorAll('[data-cp-darkmode-toggle]').forEach(function (btn) {
      btn.setAttribute('aria-pressed', pressed);
      btn.setAttribute('title', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
    });
  }

  function toggle() {
    setTheme(getTheme() === 'dark' ? 'light' : 'dark');
  }

  function init() {
    syncButtons(getTheme());
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-cp-darkmode-toggle]');
      if (btn) {
        e.preventDefault();
        toggle();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // expose untuk akses manual: window.CPDarkMode.toggle()
  window.CPDarkMode = { toggle: toggle, setTheme: setTheme, getTheme: getTheme };
})();
