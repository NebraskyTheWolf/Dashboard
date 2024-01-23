<?php

namespace App\Orchid\Layouts;

use App\Orchid\Screens\Shop\ShopCategories;
use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ShopCategoriesLayout extends Table
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title = "Categories";

    public $target = 'shop_categories';

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('name', __('category.table.name')),
            TD::make('order', __('category.table.position')),
            TD::make('displayed', _('category.table.public'))
                ->render(function (ShopCategories $categories) {
                     if ($categories->displayed === 1) {
                         return 'Yes';
                     } else {
                         return 'No';
                     }
                })
        ];
    }
}
