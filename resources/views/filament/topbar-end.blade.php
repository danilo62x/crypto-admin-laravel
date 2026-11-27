{{-- Cluster do header (idioma / dark / notificações) — PanelsRenderHook::TOPBAR_END.
     Dropdowns via Alpine (que o Filament já carrega). O dark toggle é ligado pelo
     panel.js (data-tx-action="toggle-dark"). --}}
<div class="flex items-center gap-2">
    {{-- Idioma --}}
    <div x-data="{ open: false }" class="relative">
        <button type="button" @click="open = !open" aria-label="Selecionar idioma" :aria-expanded="open"
            class="flex h-11 w-11 cursor-pointer items-center justify-center rounded-full border border-themed text-base hover:bg-gray-50 dark:hover:bg-gray-800">🇧🇷</button>
        <div x-show="open" x-cloak @click.outside="open = false"
            class="absolute right-0 z-50 mt-3 w-44 rounded-xl border border-themed bg-white py-1 shadow-theme-lg dark:bg-gray-dark">
            <button type="button" class="flex w-full items-center gap-2 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-gray-50 dark:hover:bg-gray-800"><span>🇧🇷</span> Português</button>
            <button type="button" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-body hover:bg-gray-50 dark:hover:bg-gray-800"><span>🇺🇸</span> English</button>
            <button type="button" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-body hover:bg-gray-50 dark:hover:bg-gray-800"><span>🇪🇸</span> Español</button>
        </div>
    </div>

    {{-- Dark mode --}}
    <button type="button" data-tx-action="toggle-dark" title="Alternar tema" aria-label="Alternar tema claro e escuro"
        class="flex h-11 w-11 cursor-pointer items-center justify-center rounded-full border border-themed text-muted hover:bg-gray-50 dark:hover:bg-gray-800">
        <span data-tx-darkicon><x-icon name="moon" size="18" /></span>
    </button>

    {{-- Notificações (§9: o badge limpa quando o usuário abre/visita) --}}
    <div x-data="{ open: false, unread: true }" class="relative">
        <button type="button" @click="open = !open; unread = false" aria-label="Notificações" :aria-expanded="open"
            class="relative flex h-11 w-11 cursor-pointer items-center justify-center rounded-full border border-themed text-muted hover:bg-gray-50 dark:hover:bg-gray-800">
            <x-icon name="bell" size="18" />
            <span x-show="unread" class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-error-500" aria-hidden="true"></span>
        </button>
        <div x-show="open" x-cloak @click.outside="open = false"
            class="absolute right-0 z-50 mt-3 w-80 rounded-xl border border-themed bg-white shadow-theme-lg dark:bg-gray-dark">
            <div class="flex items-center justify-between border-b border-themed px-4 py-3">
                <p class="text-sm font-semibold text-heading">Notificações</p>
                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/15">3 novas</span>
            </div>
            <div class="max-h-80 divide-y divide-themed overflow-auto">
                @foreach ([
                    ['Novo pedido recebido', 'Pedido #1042 · há 5 min', 'shopping-bag'],
                    ['Pagamento confirmado', 'R$ 2.480,00 · há 1 h', 'dollar'],
                    ['Novo cliente cadastrado', 'Marina Costa · há 3 h', 'user'],
                ] as $n)
                    <a href="#" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/15"><x-icon :name="$n[2]" size="16" /></span>
                        <span>
                            <span class="block text-sm font-medium text-heading">{{ $n[0] }}</span>
                            <span class="block text-xs text-muted">{{ $n[1] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
            <a href="#" class="block border-t border-themed px-4 py-2.5 text-center text-sm font-medium text-brand-600 hover:bg-gray-50 dark:hover:bg-gray-800">Ver todas</a>
        </div>
    </div>
</div>
