<?php

namespace App\Orchid\Screens\Shop;

use App\Orchid\Layouts\Shop\ShopSalesList;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class ShopSales extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'sales' => \App\Models\Shop\Internal\ShopSales::paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return __('sales.screen.title');
    }

    public function permission(): ?iterable
    {
        return [
            'platform.shop.sales.read',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('sales.screen.button.add'))
                ->icon('bs.plus-circle')
                ->href(route('platform.shop.sales.edit')),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            ShopSalesList::class
        ];
    }
}
