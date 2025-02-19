<?php

namespace App\Orchid\Screens\Shop;

use App\Events\UpdateAudit;
use App\Models\Shop\Customer\ShopCustomer;
use App\Orchid\Layouts\Shop\ShopOrderLayout;
use App\Orchid\Layouts\Shop\ShopVoucherLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpFoundation\Request;

class CustomerEditScreen extends Screen
{

    public $customer;

    /**
     * Retrieves the data related to a given ShopCustomer.
     *
     * @param ShopCustomer $customer The ShopCustomer object to retrieve data for.
     * @return iterable An array containing the customer, shop orders, and vouchers related to the given customer.
     */
    public function query(ShopCustomer $customer): iterable
    {
        return [
            'customer' => $customer,
            'shop_orders' => $customer->orders()->paginate(),
            'vouchers' => $customer->vouchers()->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Edit customer account.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('bs.pencil')
                ->method('save')
                ->type(Color::SUCCESS)
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
            Layout::rows([
                Group::make([
                    Input::make('customer.username')
                        ->title('Username')
                        ->value('@' . $this->customer->username)
                        ->disabled(),

                    Input::make('customer.first_name')
                        ->title('First Name')
                        ->disabled(),

                    Input::make('customer.middle_name')
                        ->title('Middle Name')
                        ->disabled(),

                    Input::make('customer.last_name')
                        ->title('Last Name')
                        ->disabled(),
                ])->alignStart(),

                Group::make([
                    Input::make('customer.phone')
                        ->title('Phone Number')
                        ->disabled(),
                    Input::make('customer.email')
                        ->title('Email')
                        ->disabled()
                ])->alignCenter(),
            ]),

            ShopOrderLayout::class,
            ShopVoucherLayout::class
        ];
    }

    /**
     * Saves the customer data from the request and redirects to the customers page.
     *
     * @param \Illuminate\Http\Request $request The request object containing the customer data.
     * @return RedirectResponse The redirect response to the customers page.
     */
    public function save(Request $request): RedirectResponse
    {
        $this->customer->fill($request->get('customer'))->save();

        Toast::info("You saved " . $this->customer->username . ' account');

        event(new UpdateAudit('customer', 'Updated ' . $this->customer->username . ' account', Auth::user()->name));

        return redirect()->route('platform.shop.customers');
    }
}
