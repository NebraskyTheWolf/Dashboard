<?php
declare(strict_types=1);

namespace App\Orchid;

use App\Models\Event\Events;
use App\Models\Shop\Customer\Order\ShopOrders;
use App\Models\Shop\Customer\ShopCustomer;
use App\Models\Shop\Internal\ShopSettings;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);
    }

    /**
     * @return array|Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Nastavení')
                ->icon('bs.gear')
                ->title('Navigace')
                ->list([
                    Menu::make('Sociální sítě')
                        ->icon('bs.person-walking')
                        ->route('platform.social.list')
                        ->permission('platform.systems.social'),
                    Menu::make('Zařízení')
                        ->badge(fn () => "Beta", Color::WARNING)
                        ->icon('bs.phone')
                        ->route('platform.device')
                        ->permission('platform.device'),
                    Menu::make("Uživatelé")
                        ->icon('bs.people')
                        ->route('platform.systems.users')
                        ->permission('platform.systems.users')
                        ->title("Správa přístupu"),
                    Menu::make("Role a oprávnění")
                        ->icon('bs.shield')
                        ->route('platform.systems.roles')
                        ->permission('platform.systems.roles'),
                    Menu::make("Auditové protokoly")
                        ->icon('bs.clipboard2')
                        ->route('platform.audit')
                        ->permission('platform.audit.read'),

                    Menu::make("Real Time Service")
                        ->icon('bs.database')
                        ->route('platform.realtime')
                        ->permission('platform.systems.realtime'),
                ])
                ->divider()
                ->permission('platform.systems.settings'),

            Menu::make('OAuth')
                ->icon('bs.gear')
                ->badge(fn () => "Nové", Color::SECONDARY)
                ->title('Autentizace')
                ->list([
                    Menu::make('Aplikace')
                        ->icon('bs.boxes')
                        ->route('platform.application.list'),
                    Menu::make('Scope')
                        ->icon('bs.bounding-box-circles')
                        ->route('platform.scope.list')
                        ->permission('auth.scope.read'),
                    Menu::make('Skupiny rozsahů')
                        ->icon('bs.bookmark-star')
                        ->route('platform.scope_group.list')
                        ->permission('auth.scope_group.read'),
                ])->permission('platform.systems.applications')->divider(),

            Menu::make('Přílohy')
                ->icon('bs.archive')
                ->list([
                    Menu::make('Soubory')
                        ->icon('bs.images')
                        ->route('platform.attachments')
                        ->permission('platform.systems.attachments.files'),
                    Menu::make("Zprávy & DMCA")
                        ->icon('bs.exclamation-octagon')
                        ->route('platform.reports')
                        ->permission('platform.systems.attachments.reports'),
                ])
                ->divider()
                ->permission('platform.systems.attachments'),

            Menu::make('Hlavní stránka')
                ->icon('bs.chat-right-text')
                ->list([
                    Menu::make('Zprávy')
                        ->icon('bs.book')
                        ->route('platform.post.list')
                        ->permission('platform.systems.posts'),
                    Menu::make('Odeslat e-mail')
                        ->icon('bs.envelope')
                        ->route('platform.admin.sendmail')
                        ->permission('platform.systems.email')
                        ->badge(fn () => "Nové", Color::SECONDARY),
                    Menu::make('Události')
                        ->icon('bs.calendar-event')
                        ->route('platform.events.list')
                        ->slug('events')
                        ->badge(fn () => Events::where('status', 'INCOMING')->orWhere('status', 'STARTED')->count() ?: 0)
                        ->permission('platform.systems.events'),
                    Menu::make('Stránky')
                        ->icon('bs.file-earmark')
                        ->route('platform.pages.list')
                        ->permission('platform.systems.pages')
                ])
                ->divider()
                ->permission('platform.systems.posts'),

            Menu::make('Účetnictví')
                ->icon('bs.calculator')
                ->list([
                    Menu::make('Domů')
                        ->icon('bs.house')
                        ->route('platform.accounting.main'),

                    Menu::make('Měsíční zpráva')
                        ->icon('bs.briefcase')
                        ->route('platform.shop.reports'),

                    Menu::make('Zpráva o transakcích')
                        ->icon('bs.graph-up')
                        ->route('platform.accounting.transactions.reports'),

                    Menu::make('Účetní výkaz')
                        ->icon('bs.buildings')
                        ->route('platform.accounting.reports'),

                    Menu::make('Transakce')
                        ->icon('bs.arrow-left-right')
                        ->route('platform.accounting.transactions'),

                    Menu::make('Faktury')
                        ->icon('bs.card-checklist')
                        ->route('platform.accounting.invoices'),
                ])
                ->divider()
                ->badge(fn () => "Nové", Color::SECONDARY)
                ->permission('platform.accounting.navbar'),

            Menu::make('E-Shop')
                ->icon('bs.cart2')
                ->list([
                    Menu::make('Statistika')
                        ->icon('bs.graph-up')
                        ->route('platform.shop.statistics')
                        ->title("RŮST"),
                    Menu::make('Daně')
                        ->icon('bs.exposure')
                        ->route('tax.group.list')
                        ->permission('platform.shop.taxes.navbar'),
                    Menu::make('Zákazníci')
                        ->icon('bs.card-list')
                        ->route('platform.shop.customers')
                        ->badge(fn () => ShopCustomer::count() ?: 0),
                    Menu::make('Produkty')
                        ->icon('bs.window-sidebar')
                        ->route('platform.shop.products')
                        ->permission('platform.systems.eshop.products')
                        ->title("PRODUKTY A PRODEJ"),
                    Menu::make('Kategorie')
                        ->icon('bs.window-sidebar')
                        ->route('platform.shop.categories')
                        ->permission('platform.systems.eshop.products'),
                    Menu::make('Prodej')
                        ->icon('bs.credit-card-2-front')
                        ->route('platform.shop.sales')
                        ->permission('platform.systems.eshop.sales')
                        ->canSee($this->isSalesEnabled()),
                    Menu::make('Poukazy')
                        ->icon('bs.card-list')
                        ->route('platform.shop.vouchers')
                        ->permission('platform.systems.eshop.vouchers')
                        ->canSee($this->isVouchersEnabled()),
                    Menu::make('Přepravci')
                        ->icon('bs.box-seam')
                        ->route('platform.shop.carriers')
                        ->permission('platform.systems.eshop.carriers')
                        ->title("POUKAZY"),
                    Menu::make('Země')
                        ->icon('bs.globe')
                        ->route('platform.shop.countries.list')
                        ->permission('platform.eshop.countries'),
                    Menu::make('Objednávky')
                        ->icon('bs.box-seam')
                        ->route('platform.shop.orders')
                        ->badge(fn () => ShopOrders::where('status', 'PROCESSING')->orWhere('status', 'OUTING')->count() ?: 0)
                        ->permission('platform.systems.eshop.orders')
                        ->slug('orders')
                        ->title("OBJEDNÁVKY A PODPORA"),
                    Menu::make('Nastavení')
                        ->icon('bs.gear')
                        ->route('platform.shop.settings')
                        ->permission('platform.systems.eshop.settings')
                        ->title("ŘÍZENÍ OBCHODU")
                ])
                ->divider()
                ->permission('platform.systems.eshop')
        ];
    }

    public function permissions(): array {
        return [
            ItemPermission::group("Systémy")
                ->addPermission('platform.systems.roles', "Role")
                ->addPermission('platform.systems.users', "Uživatelé")
                ->addPermission('platform.systems.settings', "Nastavení (Navigační lišta)")
                ->addPermission('platform.systems.applications', "OAuth (Pouze pro vývojáře)")
                ->addPermission('platform.audit.read', "Auditní protokoly (čtení)")
                ->addPermission('platform.systems.social', "Správa sociálních médií (čtení/zápis)")
                ->addPermission('platform.systems.dashboard', "Přístup k nástěnce (přihlášení)")
                ->addPermission('platform.systems.realtime', 'Real time services (Write)'),

            ItemPermission::group("Správa obchodu")
                ->addPermission('platform.systems.eshop', 'E-obchod (Navigační lišta)')

                ->addPermission('platform.shop.categories.read', 'Kategorie (Čtení)')
                ->addPermission('platform.shop.categories.write', 'Kategorie (Zápis)')
                ->addPermission('platform.shop.orders.read', 'Objednávky (Čtení)')
                ->addPermission('platform.shop.orders.write', 'Objednávky (Zápis)')
                ->addPermission('platform.shop.products.read', 'Produkty (Čtení)')
                ->addPermission('platform.shop.products.write', 'Produkty (Zápis)')
                ->addPermission('platform.shop.sales.read', 'Prodej (Čtení)')
                ->addPermission('platform.shop.sales.write', 'Prodej (Zápis)')
                ->addPermission('platform.shop.settings.read', 'Nastavení (Čtení)')
                ->addPermission('platform.shop.settings.write', 'Nastavení (Zápis)')
                ->addPermission('platform.shop.statistics.read', 'Statistiky (Čtení)')
                ->addPermission('platform.shop.statistics.write', 'Statistiky (Zápis)')
                ->addPermission('platform.shop.support.read', 'Podpora (Čtení)')
                ->addPermission('platform.shop.support.write', 'Podpora (Zápis)')
                ->addPermission('platform.shop.vouchers.read', 'Slevové kupóny (Čtení)')
                ->addPermission('platform.shop.vouchers.write', 'Slevové kupóny (Zápis)')

                ->addPermission('platform.shop.taxes.navbar', 'Daň (Navigační lišta)')
                ->addPermission('platform.shop.taxes.write', 'Daň (Zápis)')
                ->addPermission('platform.shop.taxes.read', 'Daň (Čtení)')

                ->addPermission('platform.systems.eshop.settings', 'Nastavení e-obchodu (čtení/zápis)')
                ->addPermission('platform.systems.eshop.support', 'Podpora e-obchodu (čtení/zápis)')
                ->addPermission('platform.systems.eshop.orders', 'Objednávky e-obchodu (čtení/zápis)')
                ->addPermission('platform.systems.eshop.products', 'Produkty e-obchodu (čtení/zápis)')
                ->addPermission('platform.systems.eshop.vouchers', 'Slevové kupóny e-obchodu (zápis)')
                ->addPermission('platform.systems.eshop.sales', 'Prodej e-obchodu (čtení/zápis)')

                ->addPermission('platform.shop.carriers.read', 'Přepravci e-obchodu (Čtení)')
                ->addPermission('platform.shop.carriers.write', 'Přepravci e-obchodu (Zápis)')

                ->addPermission('platform.shop.countries.read', 'Země e-obchodu (Čtení)')
                ->addPermission('platform.shop.countries.write', 'Země e-obchodu (Zápis)')
                ->addPermission('platform.eshop.countries', 'Země e-obchodu (Navigační lišta)')

                ->addPermission('platform.eshop.inventory.read', 'Sklad (Čtení)')
                ->addPermission('platform.eshop.inventory.write', 'Sklad (Zápis)')

                ->addPermission('platform.systems.eshop.carriers', 'Přepravci e-obchodu (Navigační lišta)'),

            ItemPermission::group('Přílohy')
                ->addPermission('platform.systems.attachments.files', 'Soubory (Čtení)')
                ->addPermission('platform.systems.attachments.files.write', 'Soubory (Zápis)')
                ->addPermission('platform.systems.attachments.reports', 'Zprávy (Čtení)')
                ->addPermission('platform.systems.attachments.reports.write', 'Zprávy (Zápis)')
                ->addPermission('platform.systems.attachments', 'Přílohy (Navigační lišta)'),

            ItemPermission::group('Účetnictví')
                ->addPermission('platform.accounting.monthly_report', 'Měsíční zprávy (Čtení/Zápis)')
                ->addPermission('platform.accounting', 'Hlavní (Čtení)')
                ->addPermission('platform.accounting.navbar', 'Přístup (Navigační lišta)')
                ->addPermission('platform.accounting.invoices', 'Faktury (Čtení/Zápis)')
                ->addPermission('platform.accounting.transactions', 'Transakce (Čtení/Zápis)'),

            ItemPermission::group('Správa zařízení')
                ->addPermission("platform.device", "Zařízení (Přístup)"),

            ItemPermission::group("Správa stránek a událostí")
                ->addPermission('platform.systems.posts', "Příspěvky (Navigační lišta)")
                ->addPermission('platform.systems.events', "Události (Navigační lišta)")
                ->addPermission('platform.systems.pages', "Stránky (Navigační lišta)")
                ->addPermission('platform.systems.gallery', "Fotogalerie (Navigační lišta)")

                ->addPermission('platform.systems.pages.read', "Stránky (čtení)")
                ->addPermission('platform.systems.pages.write', "Stránky (zápis)")

                ->addPermission('platform.systems.events.read', "Události (čtení)")
                ->addPermission('platform.systems.events.write', "Události (zápis)")

                ->addPermission('platform.systems.post.read', "Příspěvky (čtení)")
                ->addPermission('platform.systems.post.write', "Příspěvky (zápis)")
                ->addPermission('platform.systems.email', "Odeslat e-mail"),

            ItemPermission::group("Firebase Messaging")
                ->addPermission('platform.firebase.token.read', 'Token (Čtení)')
                ->addPermission('platform.firebase.token.write', 'Token (Zápis)')
                ->addPermission('platform.firebase.subscribe', 'Odběr (Čtení/Přítomnost)')
                ->addPermission('platform.firebase.notification.ack', 'Oznámení (ACK)'),

            ItemPermission::group('Kalendář & Agenda')
                ->addPermission('api.calendar.add', 'Přidat událost (API)')
                ->addPermission('api.calendar.update', 'Aktualizovat událost (API)')
                ->addPermission('api.calendar.remove', 'Odstranit událost (API)'),

            ItemPermission::group('OAuth platforma')
                ->addPermission('auth.scope.read', 'Rozsah (Čtení)')
                ->addPermission('auth.scope.write', 'Rozsah (Zápis)')

                ->addPermission('auth.scope_group.read', 'Skupina rozsahů (Čtení)')
                ->addPermission('auth.scope_group.write', 'Skupina rozsahů (Zápis)')

                ->addPermission('auth.application.read', 'Aplikace (Čtení)')
                ->addPermission('auth.application.write', 'Aplikace (Zápis)'),

            ItemPermission::group('Správa botů')
                ->addPermission('platform.navbar', 'Navigační lišta (Zobrazení)')
        ];
    }

    private function isSalesEnabled(): bool
    {
        return ShopSettings::latest()->first()->shop_sales;
    }

    private function isVouchersEnabled(): bool
    {
        return ShopSettings::latest()->first()->shop_vouchers;
    }
}
