<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;

use App\Models\ShopSettings;

use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;


class ShopSettingsScreen extends Screen
{

    public $settings;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'settings' => ShopSettings::where('id', 0)->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Shop Settings';
    }

    public function permission(): iterable
    {
        return [
            'platform.system.eshop.settings.write'
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [];
    }


    public function update(Request $request) {
        $this->settings->fill($request->get('settings'))->save();

        Toast::info('You have successfully updated the shop settings.');

        return redirect()->route('platform.shop.settings');
    }
}
