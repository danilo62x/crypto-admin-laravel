<?php

namespace App\Filament;

use Filament\Pages\Page;

/**
 * Base para as páginas de conteúdo portadas do tailwind/html.
 *
 * O markup portado (resources/views/filament/pages/content/*.blade.php) já traz
 * seu próprio título/subtítulo e usa as utilities do app.css (carregado no painel
 * via render hook no AdminPanelProvider). Por isso escondemos o header e os
 * breadcrumbs nativos do Filament — o conteúdo fica idêntico ao tailwind/html.
 *
 * Fica em app/Filament/ (NÃO em app/Filament/Pages/) para não ser registrada
 * como página pelo discoverPages().
 */
abstract class BaseContentPage extends Page
{
    public function getHeading(): string
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
