<?php

namespace App\Orchid\Screens\Shop;

use App\Events\OrderUpdateEvent;
use App\Events\UpdateAudit;
use app\Models\Shop\Customer\Order\OrderedProduct;
use App\Models\Shop\Customer\Order\OrderPayment;
use App\Models\Shop\Customer\Order\ShopOrders;
use App\Models\Shop\Internal\ShopCountries;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ShopOrderEdit extends Screen
{
    public $order;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param ShopOrders $order
     * @return iterable
     */
    public function query(ShopOrders $order): iterable
    {
        return [
            'order' => $order,
            'orderPayment' => $order->payments()->paginate(),
            'orderProducts' => $order->orderedProducts()->paginate(),
            'orderCarrier' => $order->carrier(),
            'orderSales' => $order->sales(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->order->customer()->first_name . ' ' . __('Order');
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        if ($this->order->status === "PENDING_APPROVAL") {
            return [
                Button::make(__('Approve'))
                    ->icon('bs.check2-square')
                    ->type(Color::SUCCESS)
                    ->confirm(__('Are you sure you want to approve this order?'))
                    ->method('approve'),
                Button::make(__('Reject'))
                    ->icon('bs.x-square')
                    ->type(Color::DANGER)
                    ->confirm(__('Are you sure you want to reject this order?'))
                    ->method('reject')
            ];
        } else {
            return [
                Button::make(__('Set as completed'))
                    ->icon('bs.check2-square')
                    ->type(Color::SUCCESS)
                    ->confirm(__('Are you sure you want to mark this order as completed?'))
                    ->method('completed')
                    ->disabled($this->order->status === "COMPLETED" || $this->order->status === "REFUNDED"),
                Button::make(__('Set as delivered'))
                    ->icon('bs.envelope-fill')
                    ->type(Color::SUCCESS)
                    ->confirm(__('Are you sure you want to mark this order as delivered?'))
                    ->method('delivered')
                    ->disabled($this->order->status === "DELIVERED" || $this->order->status === "REFUNDED"),
                Button::make(__('Issue Refund'))
                    ->icon('bs.slash-circle')
                    ->type(Color::PRIMARY)
                    ->confirm(__('Are you sure you want to issue a refund for this order?'))
                    ->method('issueRefund')
                    ->disabled($this->order->status === "REFUNDED"),
            ];
        }
    }

    /**
     * The screen's layout elements.
     *
     * @return array
     */
    public function layout(): iterable
    {
        return [
            Layout::tabs([
                __('Order Information') => [
                    Layout::legend('order', [
                        Sight::make(__('Full Name'))
                            ->render(fn() => $this->order->customer()->first_name . ' ' . $this->order->customer()->last_name),
                        Sight::make('first_address', __('First Address'))
                            ->render(fn() => $this->order->address()->address_one),
                        Sight::make('second_address', __('Second Address'))
                            ->render(fn() => $this->order->address()->address_two),
                        Sight::make('postal_code', __('Postal Code'))
                            ->render(fn() => $this->order->address()->zip),
                        Sight::make('country', __('Country'))
                            ->render(fn() => $this->getCountry()),
                        Sight::make('email', __('Email'))
                            ->render(fn() => $this->order->customer()->email),
                        Sight::make('phone_number', __('Phone Number'))
                            ->render(fn() => $this->order->customer()->phone),
                        Sight::make('status', __('Status'))
                            ->render(fn() => $this->orderStatus()),
                    ])->title(__('Contact & Address')),

                    Layout::legend('orderCarrier', [
                        Sight::make('carrier_name', __('Carrier Name'))
                            ->render(fn() => $this->order->carrier()->carrierName),
                        Sight::make('carrier_delay', __('Carrier Delay'))
                            ->render(fn() => $this->order->carrier()->carrierDelay . ' (Business Days)'),
                        Sight::make('carrier_price', __('Carrier Price'))
                            ->render(fn() => $this->order->carrier()->carrierPrice . ' Kc'),
                    ])->title(__('Carrier'))
                        ->canSee($this->order->carrier() !== null),
                ],
                __('Payment') => [
                    \App\Orchid\Layouts\Shop\OrderPayment::class,
                ],
                __('Ordered Products') => [
                    Layout::table('orderProducts', [
                        TD::make('product_name', 'Product Name')
                            ->render(function (OrderedProduct $product) {
                                return $product->getProduct()->name;
                            }),
                        TD::make('quantity', 'Quantity')
                            ->render(function (OrderedProduct $product) {
                                return number_format($product->quantity);
                            }),
                        TD::make('price','Price')
                            ->render(function (OrderedProduct $product) {
                                return $this->getProductPrice($product);
                            }),
                        TD::make('tax','Tax (%)')
                            ->render(function (OrderedProduct $product) {
                                return $this->getProductTax($product);
                            }),
                        TD::make('discount', 'Discount (%)')
                            ->render(function (OrderedProduct $product) {
                                return $this->getProductDiscount($product);
                            })
                    ])->title(__('Products')),
                ]
            ])->activeTab(__('Order Information'))
        ];
    }

    private function orderStatus(): string
    {
        $statuses = [
            "PENDING_APPROVAL" => ['label' => 'ui orange label', 'text' => 'Pending Approval <i class="loading cog icon"></i>'],
            "PROCESSING" => ['label' => 'ui blue label', 'text' => 'Processing <i class="loading cog icon"></i>'],
            "CANCELLED" => ['label' => 'ui red label', 'text' => 'Cancelled'],
            "REFUNDED" => ['label' => 'ui orange label', 'text' => 'Refunded'],
            "DISPUTED" => ['label' => 'ui red label', 'text' => 'Disputed'],
            "DELIVERED" => ['label' => 'ui green label', 'text' => 'Delivered'],
            "ARCHIVED" => ['label' => 'ui brown label', 'text' => 'Archived'],
            "COMPLETED" => ['label' => 'ui green label', 'text' => 'Completed'],
            "OUTING" => ['label' => 'ui blue label', 'text' => 'Ready for pickup <i class="loading cog icon"></i>'],
            "OUTING_DELIVERED" => ['label' => 'ui blue label', 'text' => 'Picked up'],
            "DENIED" => ['label' => 'ui red label', 'text' => 'Cancelled'],
        ];

        $status = $this->order->status;
        return '<a class="' . $statuses[$status]['label'] . '">' . $statuses[$status]['text'] . ' </a>';
    }

    private function getCountry(): string
    {
        $country = ShopCountries::where('iso_code', $this->order->address()->country)->first();
        return $country ? $country->country_name : $this->order->address()->country;
    }

    public function completed(): RedirectResponse
    {
        $this->order->update(['status' => 'COMPLETED']);
        event(new OrderUpdateEvent($this->order, 'completed'));
        event(new UpdateAudit('orders', 'Set as Completed ' . $this->order->customer()->first_name, Auth::user()->name));
        return redirect()->route('platform.shop.orders');
    }

    public function delivered(): RedirectResponse
    {
        $this->order->update(['status' => 'DELIVERED']);
        event(new OrderUpdateEvent($this->order, 'delivered'));
        event(new UpdateAudit('orders', 'Set as Delivered ' . $this->order->customer()->first_name, Auth::user()->name));
        return redirect()->route('platform.shop.orders');
    }

    public function issueRefund(): RedirectResponse
    {
        $this->order->update(['status' => 'REFUNDED']);
        $payment = $this->getOrderPayment();

        if ($payment === null) {
            Toast::error("This order has not been paid and cannot be refunded.");
            return redirect()->route('platform.shop.orders');
        }
        if (str_contains($payment->provider, 'Poukaz')) {
            Toast::error("This order was paid using a voucher code and cannot be refunded.");
            return redirect()->route('platform.shop.orders');
        }

        OrderPayment::create([
            'order_id' => $this->order->order_id,
            'status' => 'REFUNDED',
            'transaction_id' => $payment->transaction_id,
            'provider' => $payment->provider,
            'price' => $payment->price,
        ]);

        Toast::success(__('You refunded :name of :price Kc', ['name' => $this->order->customer()->first_name, 'price' => $payment->price]));
        event(new OrderUpdateEvent($this->order, 'refund'));
        event(new UpdateAudit('orders', 'Refunded ' . $this->order->customer()->first_name, Auth::user()->name));
        return redirect()->route('platform.shop.orders');
    }

    private function getOrderPayment(): OrderPayment
    {
        return OrderPayment::where('order_id', $this->order->order_id)->first();
    }

    private function getProductPrice($product): string
    {
        return number_format($product->getProduct()->getNormalizedPrice() * $product->quantity, 2);
    }

    private function getProductTax($product): string
    {
        return $product->getProduct()->getProductTax() . '%';
    }

    private function getProductDiscount($product): string
    {
        return $product->getProduct()->getProductSale() . '%';
    }
}
