/* ============================================================================
 *  panel.js — bootstrap de UI do painel Filament (Tailwind Admin).
 *
 *  Reconstrói, sobre o DOM do Filament, os recursos interativos que viviam no
 *  shell do tailwind/html (state.js + layout.js):
 *   - UIState: tema custom (vars --tx- e brand), dark mode, tamanho de fonte e largura
 *     da sidebar, persistidos em localStorage 'ui-state'. As páginas portadas
 *     (ex.: Configurações) chamam window.UIState / setThemeVar / notify.
 *   - notify(): toasts.
 *   - resize da sidebar (arrastar a borda direita).
 *   - header: dark toggle (os demais botões/dropdowns são Alpine no HTML injetado).
 *
 *  Carregado via render hook (HEAD_END) no AdminPanelProvider, depois do icons.js.
 * ========================================================================== */
(function () {
  'use strict';

  // ---------------------------------------------------------------- UIState
  var STORAGE_KEY = 'ui-state';
  var DEFAULTS = { darkMode: false, sidebarWidth: 290, fontSize: 'base', customTheme: {} };
  var state = Object.assign({}, DEFAULTS);
  var listeners = new Set();

  function load() {
    try { var raw = localStorage.getItem(STORAGE_KEY); if (raw) state = Object.assign({}, DEFAULTS, JSON.parse(raw)); } catch (e) {}
    // Dark mode é compartilhado com o Filament (localStorage 'theme'). Default
    // CLARO (como o tailwind/html) quando nunca escolhido — evita seguir o tema
    // do sistema/headless. O toggle continua salvando 'dark'/'light'.
    try {
      if (localStorage.getItem('theme') === null) localStorage.setItem('theme', 'light');
      state.darkMode = localStorage.getItem('theme') === 'dark';
    } catch (e) {}
  }
  function syncFilamentTheme(on) {
    try { localStorage.setItem('theme', on ? 'dark' : 'light'); } catch (e) {}
    try { if (window.Alpine && typeof Alpine.store === 'function' && Alpine.store('theme') !== undefined) Alpine.store('theme', on ? 'dark' : 'light'); } catch (e) {}
  }
  function persist() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        darkMode: state.darkMode, sidebarWidth: state.sidebarWidth,
        fontSize: state.fontSize, customTheme: state.customTheme,
      }));
    } catch (e) {}
  }
  function emit() { listeners.forEach(function (cb) { cb(state); }); }
  function set(patch) {
    if (patch && Object.prototype.hasOwnProperty.call(patch, 'darkMode')) syncFilamentTheme(!!patch.darkMode);
    state = Object.assign({}, state, patch); persist(); emit();
  }
  function get() { return state; }
  function subscribe(cb) { listeners.add(cb); return function () { listeners.delete(cb); }; }

  function applyFontSize() {
    var sizes = { sm: '13px', base: '14px', lg: '15px', xl: '16px' };
    document.documentElement.style.fontSize = sizes[state.fontSize] || '14px';
  }
  function applyTheme() {
    var root = document.documentElement;
    if (state.darkMode) root.classList.add('dark'); else root.classList.remove('dark');
    Object.entries(state.customTheme || {}).forEach(function (e) { root.style.setProperty(e[0], e[1]); });
    applyFilamentPrimary(getThemeVar('--color-brand-500'));
    applyFontSize();
  }
  function setThemeVar(key, val) {
    state.customTheme = Object.assign({}, state.customTheme, {}); state.customTheme[key] = val;
    document.documentElement.style.setProperty(key, val);
    if (key === '--color-brand-500') applyFilamentPrimary(val);
    persist(); emit();
  }
  function resetThemeVars() {
    Object.keys(state.customTheme || {}).forEach(function (k) { document.documentElement.style.removeProperty(k); });
    set({ customTheme: {} });
    applyFilamentPrimary(THEME_DEFAULTS['--color-brand-500']); // volta o chrome ao indigo
  }
  var THEME_DEFAULTS = {
    '--color-brand-500': '#465fff', '--color-brand-600': '#3641f5',
    '--color-success-500': '#12b76a', '--color-warning-500': '#f79009',
    '--color-error-500': '#f04438', '--color-blue-light-500': '#0ba5ec',
    '--tx-sidebar-bg': '#ffffff', '--tx-sidebar-text': '#475467', '--tx-sidebar-active-bg': '#ecf3ff',
    '--tx-body-bg': '#f9fafb', '--tx-card-bg': '#ffffff', '--tx-header-bg': '#ffffff',
    '--tx-border': '#e4e7ec', '--tx-text': '#344054', '--tx-text-heading': '#101828', '--tx-text-muted': '#667085',
  };
  function getThemeVar(key) { return (state.customTheme && state.customTheme[key]) || THEME_DEFAULTS[key] || '#000000'; }

  // Re-tematiza o CHROME do Filament. O Filament colore sidebar/botões/badges via
  // --primary-50..950 no formato "R G B". Geramos essa escala a partir do brand-500
  // pra que mudar a cor nas Configurações mude o SISTEMA inteiro, não só o conteúdo.
  function hexToRgb(hex) {
    hex = String(hex).replace('#', '');
    if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    var n = parseInt(hex, 16);
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
  }
  function mix(a, b, t) { return [0, 1, 2].map(function (i) { return Math.round(a[i] + (b[i] - a[i]) * t); }); }
  function applyFilamentPrimary(hex) {
    if (!hex || String(hex).indexOf('#') !== 0) return;
    var base; try { base = hexToRgb(hex); } catch (e) { return; }
    var W = [255, 255, 255], B = [0, 0, 0];
    var scale = { 50: ['w', 0.95], 100: ['w', 0.9], 200: ['w', 0.78], 300: ['w', 0.62], 400: ['w', 0.34],
      500: ['b', 0], 600: ['b', 0.12], 700: ['b', 0.28], 800: ['b', 0.42], 900: ['b', 0.55], 950: ['b', 0.72] };
    Object.keys(scale).forEach(function (k) {
      var t = scale[k][1];
      var rgb = t === 0 ? base : mix(base, scale[k][0] === 'w' ? W : B, t);
      // Formato VÍRGULA — o Filament usa rgba(var(--primary-500), <alpha>); espaço quebraria.
      document.documentElement.style.setProperty('--primary-' + k, rgb.join(', '));
    });
  }

  load();
  applyTheme(); // aplica cedo (documentElement já existe no <head>) — sem flash

  // ------------------------------------------------------------------ toast
  function icon(n, o) { return window.icon ? window.icon(n, o) : ''; }
  var TOAST_STYLES = {
    success: { border: 'border-success-200 dark:border-success-500/30', icon: 'check', color: 'text-success-500' },
    error: { border: 'border-error-200 dark:border-error-500/30', icon: 'x', color: 'text-error-500' },
    warning: { border: 'border-warning-200 dark:border-warning-500/30', icon: 'bell', color: 'text-warning-500' },
    info: { border: 'border-blue-light-200 dark:border-blue-light-500/30', icon: 'bell', color: 'text-blue-light-500' },
  };
  function stackEl() {
    var s = document.getElementById('tx-toast-stack');
    if (!s) { s = document.createElement('div'); s.id = 'tx-toast-stack';
      s.className = 'pointer-events-none fixed bottom-6 right-6 z-[9999] flex flex-col gap-3'; document.body.appendChild(s); }
    return s;
  }
  function notify(message, type, duration) {
    type = type || 'info'; duration = duration || 4000;
    var s = TOAST_STYLES[type] || TOAST_STYLES.info; var stack = stackEl();
    var el = document.createElement('div');
    el.className = 'pointer-events-auto flex w-80 items-start gap-3 rounded-lg border bg-white p-4 shadow-theme-md dark:bg-gray-dark ' + s.border;
    el.innerHTML = '<span class="' + s.color + '">' + icon(s.icon, { size: 20 }) + '</span>'
      + '<div class="flex-1"><p class="text-sm font-medium text-heading">' + message + '</p></div>'
      + '<button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">' + icon('x', { size: 16 }) + '</button>';
    el.querySelector('button').addEventListener('click', function () { el.remove(); });
    stack.appendChild(el); setTimeout(function () { el.remove(); }, duration);
  }

  // expõe globais que as páginas portadas usam
  window.UIState = { get: get, set: set, subscribe: subscribe, applyTheme: applyTheme, applyFontSize: applyFontSize,
    setThemeVar: setThemeVar, resetThemeVars: resetThemeVars, getThemeVar: getThemeVar, THEME_DEFAULTS: THEME_DEFAULTS, notify: notify };
  window.notify = notify;
  window.notifySuccess = function (m) { notify(m, 'success'); };
  window.notifyError = function (m) { notify(m, 'error'); };
  window.notifyWarning = function (m) { notify(m, 'warning'); };

  // -------------------------------------------------------- sidebar + header
  function sidebar() { return document.querySelector('.fi-sidebar'); }
  // Largura custom da sidebar: injeta a CSS var --sidebar-width (que o Filament usa
  // em w-[--sidebar-width] na sidebar EXPANDIDA) DENTRO de um @media (min-width:1024px),
  // ou seja, só no DESKTOP. Não seta width inline no elemento (o inline vencia o
  // collapse nativo) e não vale no MOBILE (lá o Filament usa a largura padrão do drawer).
  // Resultado: largura custom só na sidebar expandida do desktop; ao colapsar o Filament
  // aplica a largura colapsada; no celular fica o drawer/overlay padrão.
  function applySidebarWidth() {
    var w = get().sidebarWidth;
    var id = 'tx-sidebar-width-style';
    var el = document.getElementById(id);
    if (!el) { el = document.createElement('style'); el.id = id; document.head.appendChild(el); }
    el.textContent = '@media (min-width:1024px){:root{--sidebar-width:' + w + 'px}}';
  }
  function initResize() {
    var sb = sidebar(); if (!sb || sb.querySelector('.tx-resize-handle')) return;
    // NÃO setar position:relative inline aqui — o Filament já dá `lg:sticky` (desktop) e
    // `fixed` (drawer mobile) à .fi-sidebar; ambos criam contexto p/ o handle absolute.
    // O relative inline vencia o `fixed` e quebrava o drawer no celular (meio aberto).
    var h = document.createElement('div');
    h.className = 'tx-resize-handle';
    h.title = 'Arraste para redimensionar';
    h.style.cssText = 'position:absolute;top:0;right:-3px;width:6px;height:100%;cursor:col-resize;z-index:50';
    sb.appendChild(h);
    var dragging = false;
    h.addEventListener('mousedown', function (e) { dragging = true; document.body.style.userSelect = 'none'; e.preventDefault(); });
    window.addEventListener('mousemove', function (e) {
      if (!dragging || window.innerWidth < 1024) return; // resize só no desktop
      var w = Math.min(360, Math.max(200, e.clientX));
      state.sidebarWidth = w;
      applySidebarWidth(); // atualiza a var só no desktop (via @media), não vaza pro mobile
    });
    window.addEventListener('mouseup', function () { if (dragging) { dragging = false; document.body.style.userSelect = ''; persist(); } });
  }
  function syncDarkIcons() {
    document.querySelectorAll('[data-tx-darkicon]').forEach(function (el) {
      el.innerHTML = icon(get().darkMode ? 'sun' : 'moon', { size: 18 });
    });
  }
  function initHeader() {
    document.querySelectorAll('[data-tx-action="toggle-dark"]').forEach(function (b) {
      if (b.__txWired) return; b.__txWired = true;
      b.addEventListener('click', function () { set({ darkMode: !get().darkMode }); applyTheme(); syncDarkIcons(); });
    });
    syncDarkIcons();
  }

  function boot() { applySidebarWidth(); initResize(); initHeader(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
  subscribe(applySidebarWidth);
})();
