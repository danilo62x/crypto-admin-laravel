<x-filament-panels::page>
    {{-- conteúdo portado de resources/views/pages/settings.blade.php --}}
    <div class="space-y-6">
      <div>
        <h1 class="text-title-sm font-bold text-heading">Configurações</h1>
        <p class="mt-1 text-sm text-muted">Personalize a aparência, layout e preferências da conta.</p>
      </div>

      <section class="surface-card rounded-2xl border shadow-theme-sm">
        <header class="border-b border-themed px-5 py-4 md:px-6"><h3 class="text-base font-semibold text-heading">Aparência</h3></header>
        <div class="space-y-5 p-5 md:p-6">
          <div>
            <label class="mb-2 block text-sm font-medium text-heading">Tema</label>
            <div class="flex gap-2">
              <button data-theme-light class="inline-flex h-10 items-center gap-2 rounded-lg border border-themed px-4 text-sm font-medium text-body hover:bg-gray-50 dark:hover:bg-gray-800">Claro</button>
              <button data-theme-dark class="inline-flex h-10 items-center gap-2 rounded-lg border border-themed px-4 text-sm font-medium text-body hover:bg-gray-50 dark:hover:bg-gray-800">Escuro</button>
            </div>
          </div>
          <div class="border-t border-themed pt-5">
            <label class="mb-2 block text-sm font-medium text-heading">Tamanho da fonte</label>
            <div class="flex flex-wrap gap-2" id="font-sizes"></div>
          </div>
        </div>
      </section>

      <section class="surface-card rounded-2xl border shadow-theme-sm">
        <header class="border-b border-themed px-5 py-4 md:px-6"><h3 class="text-base font-semibold text-heading">Paletas de cores</h3></header>
        <div id="palettes" class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 md:p-6"></div>
      </section>

      <section class="surface-card rounded-2xl border shadow-theme-sm">
        <header class="border-b border-themed px-5 py-4 md:px-6">
          <h3 class="text-base font-semibold text-heading">Personalização avançada</h3>
          <p class="mt-0.5 text-xs text-muted">Ajuste cada cor individualmente</p>
        </header>
        <div id="theme-groups" class="space-y-5 p-5 md:p-6"></div>
        <div class="flex items-center gap-3 border-t border-themed px-5 py-4 md:px-6">
          <button id="reset-theme" type="button" class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-themed px-3 text-xs font-medium text-body hover:bg-gray-50 dark:hover:bg-gray-800">
            <span id="reset-icon"></span> Restaurar padrões
          </button>
          <span class="text-xs text-muted">Remove todas as customizações e restaura as cores originais</span>
        </div>
      </section>

      <section class="surface-card rounded-2xl border shadow-theme-sm">
        <header class="border-b border-themed px-5 py-4 md:px-6"><h3 class="text-base font-semibold text-heading">Layout</h3></header>
        <div class="space-y-5 p-5 md:p-6">
          <div>
            <div class="mb-2 flex items-center justify-between">
              <label class="text-sm font-medium text-heading">Largura do menu</label>
              <span id="width-label" class="rounded-md bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">290px</span>
            </div>
            <input id="width-range" type="range" min="200" max="360" step="5" value="290" class="w-full" />
            <div class="mt-1 flex justify-between text-xs text-muted"><span>200px</span><span>360px</span></div>
          </div>
        </div>
      </section>
    </div>

@push('scripts')
@verbatim
  <script>
    (function () {
      // ── Adapter: bridge the original UIState API onto the Alpine `ui` store ──
      const store = (window.Alpine && Alpine.store) ? Alpine.store('ui') : null;
      const FONT_KEY = 'app.ui.fontSize';
      const FONT_PX = { sm: '14px', base: '16px', lg: '18px', xl: '20px' };

      function notify(msg, type) {
        if (store && typeof store.notify === 'function') return store.notify(msg, type);
        if (window.notify) return window.notify(msg, type);
      }
      function getFontSize() {
        try { return localStorage.getItem(FONT_KEY) || 'base'; } catch { return 'base'; }
      }
      function applyFontSize() {
        document.documentElement.style.fontSize = FONT_PX[getFontSize()] || FONT_PX.base;
      }
      function setFontSize(v) {
        try { localStorage.setItem(FONT_KEY, v); } catch {}
      }
      function setDark(v) {
        if (store && typeof store.setDarkMode === 'function') store.setDarkMode(v);
        else document.documentElement.classList.toggle('dark', v);
      }
      function setThemeVar(k, v) {
        if (store && typeof store.setThemeVar === 'function') store.setThemeVar(k, v);
        else document.documentElement.style.setProperty(k, v);
      }
      function getThemeVar(k) {
        if (store && store.customTheme && store.customTheme[k]) return store.customTheme[k];
        return getComputedStyle(document.documentElement).getPropertyValue(k).trim();
      }
      function resetThemeVars() {
        if (store && typeof store.resetThemeVars === 'function') store.resetThemeVars();
      }
      // A largura do menu é controlada pelo window.UIState (panel.js), que é quem
      // realmente aplica a CSS var --sidebar-width na sidebar do Filament. O Alpine
      // store 'ui' tem um sidebarWidth, mas ele não está ligado à sidebar do Filament
      // (era pro shell Blade antigo) — por isso o slider não surtia efeito.
      function getSidebarWidth() {
        if (window.UIState && typeof window.UIState.get === 'function') {
          return window.UIState.get().sidebarWidth || 290;
        }
        return (store && store.sidebarWidth) ? store.sidebarWidth : 290;
      }
      function setSidebarWidth(w) {
        if (window.UIState && typeof window.UIState.set === 'function') window.UIState.set({ sidebarWidth: w });
        else if (store && typeof store.setSidebarWidth === 'function') store.setSidebarWidth(w);
      }

      // ── Font size ──
      const FONT_SIZES = [['sm', 'Pequena'], ['base', 'Normal'], ['lg', 'Grande'], ['xl', 'Extra']];

      function renderFontSizes() {
        const cur = getFontSize();
        document.getElementById('font-sizes').innerHTML = FONT_SIZES.map(([v, l]) =>
          `<button data-font="${v}" class="inline-flex h-10 items-center gap-2 rounded-lg border px-4 text-sm font-medium transition ${cur === v ? 'border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-500/15' : 'border-themed text-body hover:bg-gray-50 dark:hover:bg-gray-800'}">${l}</button>`
        ).join('');
        document.querySelectorAll('[data-font]').forEach((el) =>
          el.addEventListener('click', () => {
            setFontSize(el.getAttribute('data-font'));
            applyFontSize();
            renderFontSizes();
          })
        );
      }

      // ── Theme (dark/light) ──
      document.querySelector('[data-theme-light]').addEventListener('click', () => setDark(false));
      document.querySelector('[data-theme-dark]').addEventListener('click', () => setDark(true));

      // Every palette MUST define ALL sidebar tokens so switching palettes resets prior selection.
      const PALETTES = [
        { name: 'Indigo', vars: { '--color-brand-500': '#465fff', '--color-brand-600': '#3641f5', '--tx-sidebar-bg': '#ffffff', '--tx-sidebar-active-bg': '#ecf3ff', '--tx-sidebar-text': '#475467', '--tx-sidebar-text-active': '#465fff' } },
        { name: 'Esmeralda', vars: { '--color-brand-500': '#10b981', '--color-brand-600': '#059669', '--tx-sidebar-bg': '#0f3a2e', '--tx-sidebar-active-bg': 'rgba(16,185,129,0.18)', '--tx-sidebar-text': '#a7f3d0', '--tx-sidebar-text-active': '#ffffff' } },
        { name: 'Violeta', vars: { '--color-brand-500': '#8b5cf6', '--color-brand-600': '#7c3aed', '--tx-sidebar-bg': '#1e1b4b', '--tx-sidebar-active-bg': 'rgba(139,92,246,0.2)', '--tx-sidebar-text': '#c4b5fd', '--tx-sidebar-text-active': '#ffffff' } },
        { name: 'Tangerina', vars: { '--color-brand-500': '#f97316', '--color-brand-600': '#ea580c', '--tx-sidebar-bg': '#1c1917', '--tx-sidebar-active-bg': 'rgba(249,115,22,0.18)', '--tx-sidebar-text': '#fdba74', '--tx-sidebar-text-active': '#ffffff' } },
        { name: 'Rosa', vars: { '--color-brand-500': '#ec4899', '--color-brand-600': '#db2777', '--tx-sidebar-bg': '#2d1b35', '--tx-sidebar-active-bg': 'rgba(236,72,153,0.2)', '--tx-sidebar-text': '#f9a8d4', '--tx-sidebar-text-active': '#ffffff' } },
        { name: 'Ciano', vars: { '--color-brand-500': '#06b6d4', '--color-brand-600': '#0891b2', '--tx-sidebar-bg': '#083344', '--tx-sidebar-active-bg': 'rgba(6,182,212,0.2)', '--tx-sidebar-text': '#a5f3fc', '--tx-sidebar-text-active': '#ffffff' } },
      ];
      let activePaletteIdx = -1;
      const checkIconHtml = icon('check', { size: 20, strokeWidth: 2.5 });

      function renderPalettes() {
        document.getElementById('palettes').innerHTML = PALETTES.map((p, i) => {
          const active = i === activePaletteIdx;
          return `
            <button data-palette="${i}" class="group overflow-hidden rounded-xl border-2 text-left transition ${active ? 'border-brand-500 shadow-theme-md' : 'border-themed hover:border-brand-300 hover:shadow-theme-sm'}">
              <div class="flex h-16">
                <div class="flex flex-1 items-center justify-center text-white" style="background: ${p.vars['--color-brand-500']}">
                  ${active ? checkIconHtml : ''}
                </div>
                <div class="flex w-6 flex-col justify-center gap-1 px-1.5" style="background: ${p.vars['--tx-sidebar-bg'] || '#1a2231'}">
                  <span class="block h-[3px] rounded-sm bg-white/30"></span><span class="block h-[3px] rounded-sm bg-white/30"></span><span class="block h-[3px] rounded-sm bg-white/30"></span>
                </div>
              </div>
              <div class="px-2 py-1.5 text-center text-xs font-medium text-heading" style="background: var(--tx-card-bg)">${p.name}</div>
            </button>`;
        }).join('');
      }
      renderPalettes();

      // Click handler — delegated so it survives re-renders
      document.getElementById('palettes').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-palette]');
        if (!btn) return;
        const idx = +btn.getAttribute('data-palette');
        activePaletteIdx = idx;
        Object.entries(PALETTES[idx].vars).forEach(([k, v]) => setThemeVar(k, v));
        renderPalettes();
        notify(`Paleta "${PALETTES[idx].name}" aplicada`, 'success');
      });

      const THEME_GROUPS = [
        { title: 'Cores principais', vars: [['--color-brand-500', 'Primária'], ['--color-success-500', 'Sucesso'], ['--color-warning-500', 'Aviso'], ['--color-error-500', 'Perigo'], ['--color-blue-light-500', 'Info']] },
        { title: 'Menu lateral', vars: [['--tx-sidebar-bg', 'Fundo'], ['--tx-sidebar-text', 'Texto'], ['--tx-sidebar-active-bg', 'Fundo ativo']] },
        { title: 'Layout', vars: [['--tx-body-bg', 'Fundo da página'], ['--tx-card-bg', 'Cards'], ['--tx-header-bg', 'Header'], ['--tx-border', 'Bordas']] },
        { title: 'Tipografia', vars: [['--tx-text-heading', 'Títulos'], ['--tx-text', 'Texto principal'], ['--tx-text-muted', 'Texto secundário']] },
      ];
      document.getElementById('theme-groups').innerHTML = THEME_GROUPS.map((g) => `
        <div>
          <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-muted">${g.title}</p>
          <div class="space-y-2">
            ${g.vars.map(([key, label]) => `
              <div class="flex items-center gap-3 rounded-lg border border-themed px-3 py-2">
                <span class="flex-1 text-sm text-heading">${label}</span>
                <span class="h-5 w-5 shrink-0 rounded-md border border-themed" style="background: ${getThemeVar(key)}"></span>
                <input type="color" data-color="${key}" value="${getThemeVar(key)}" class="h-8 w-10 cursor-pointer" />
              </div>`).join('')}
          </div>
        </div>`).join('');
      document.querySelectorAll('[data-color]').forEach((el) =>
        el.addEventListener('input', () => {
          activePaletteIdx = -1; // manual tweak unselects palette
          setThemeVar(el.getAttribute('data-color'), el.value);
          renderPalettes();
        })
      );

      const resetIcon = document.getElementById('reset-icon');
      if (resetIcon) resetIcon.innerHTML = icon('reset', { size: 14 });

      document.getElementById('reset-theme').addEventListener('click', () => {
        resetThemeVars();
        location.reload();
      });

      // ── Layout: sidebar width ──
      const widthRange = document.getElementById('width-range');
      const widthLabel = document.getElementById('width-label');
      const initialWidth = getSidebarWidth();
      widthRange.value = initialWidth;
      widthLabel.textContent = initialWidth + 'px';
      widthRange.addEventListener('input', (e) => {
        const w = +e.target.value;
        setSidebarWidth(w);
        widthLabel.textContent = w + 'px';
      });

      // ── Init ──
      applyFontSize();
      renderFontSizes();
    })();
  </script>
@endverbatim
@endpush
</x-filament-panels::page>
