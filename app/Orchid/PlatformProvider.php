<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

use App\Models\Events;
use App\Models\ShopOrders;
use App\Models\ShopSupportTickets;

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

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Settings')
                ->icon('bs.gear')
                ->title('Navigation')
                ->list([
                    Menu::make('Social Media')
                        ->icon('bs.person-walking')
                        ->route('platform.example.layouts')
                        ->permission('platform.systems.social'),
                    Menu::make("Users")
                        ->icon('bs.people')
                        ->route('platform.systems.users')
                        ->permission('platform.systems.users')
                        ->title("Access Controls"),
                    Menu::make("Roles & Permissions")
                        ->icon('bs.shield')
                        ->route('platform.systems.roles')
                        ->permission('platform.systems.roles')
                ])
                ->divider()
                ->permission('platform.systems.settings'),

            Menu::make('Main page')
                ->icon('bs.chat-right-text')
                ->list([
                    Menu::make('Posts')
                        ->icon('bs.book')
                        ->route('platform.post.list')
                        ->permission('platform.systems.posts'),
                    Menu::make('Photo gallery')
                        ->icon('bs.window-sidebar')
                        ->route('platform.example.layouts')
                        ->permission('platform.systems.gallery'),
                    Menu::make('Events')
                        ->icon('bs.calendar-event')
                        ->route('platform.events.list')
                        ->badge(fn () => Events::where('status', 'INCOMING')->count() ?: 0)
                        ->permission('platform.systems.events'),
                    Menu::make('Pages')
                        ->icon('bs.file-earmark')
                        ->route('platform.example.layouts')
                        ->permission('platform.systems.pages')
                ])
                ->divider()
                ->permission('platform.systems.posts'),

            Menu::make('E-Shop')
                ->icon('bs.cart2')
                ->list([
                    Menu::make('Products')
                        ->icon('bs.window-sidebar')
                        ->route('platform.example.layouts')
                        ->permission('platform.systems.eshop.products'),
                    Menu::make('Statistics')
                        ->icon('bs.graph-up')
                        ->route('platform.example.layouts'),
                    Menu::make('Sales')
                        ->icon('bs.credit-card-2-front')
                        ->route('platform.example.layouts')
                        ->permission('platform.systems.eshop.sales'),
                    Menu::make('Vouchers')
                        ->icon('bs.card-list')
                        ->route('platform.example.layouts')
                        ->permission('platform.systems.eshop.vouchers'),
                    Menu::make('Orders')
                        ->icon('bs.box-seam')
                        ->route('platform.example.layouts')
                        ->badge(fn () => ShopOrders::where('status', 'PENDING')->count() ?: 0)
                        ->permission('platform.systems.eshop.orders'),
                    Menu::make('Support Tickets')
                        ->icon('bs.chat-right-text')
                        ->route('platform.example.layouts')
                        ->badge(fn () => ShopSupportTickets::where('status', 'PENDING')->count() ?: 0)
                        ->permission('platform.systems.eshop.support'),
                    Menu::make('Settings')
                        ->icon('bs.gear')
                        ->route('platform.example.layouts')
                        ->permission('platform.systems.eshop.settings')
                ])
                ->divider()
                ->permission('platform.systems.eshop')
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users'))
                ->addPermission('platform.systems.settings', "Settings (read/write)")

                ->addPermission('platform.systems.posts', "Posts (read/write)")
                ->addPermission('platform.systems.events', "Events (read/write)")
                ->addPermission('platform.systems.pages', "Pages (read/write)")
                ->addPermission('platform.systems.gallery', "Photos (read/write)")

                ->addPermission('platform.systems.eshop', "EShop (read)")
                ->addPermission('platform.systems.eshop.settings', "EShop Settings (read/write)")
                ->addPermission('platform.systems.eshop.support', "EShop Support (read/write)")
                ->addPermission('platform.systems.eshop.orders', "EShop Orders (read/write)")
                ->addPermission('platform.systems.eshop.products', "EShop Products (read/write)")
                ->addPermission('platform.systems.eshop.vouchers', "EShop Vouchers (write)")
                ->addPermission('platform.systems.eshop.sales', "EShop Sales (read/write)")

                ->addPermission('platform.systems.social', "Social Media Management (read/write)"),
        ];
    }
}
