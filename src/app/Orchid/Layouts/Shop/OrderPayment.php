<?php

namespace App\Orchid\Layouts\Shop;

use App\Models\OrderCarrier;
use App\Models\OrderedProduct;
use App\Models\ShopProducts;
use App\Models\ShopSales;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class OrderPayment extends Table
{
    protected $target = 'orderPayment';

    protected function columns(): iterable
    {
        return [
            TD::make('status',  __('orders.table.payment_status'))
                ->render(function (\App\Models\OrderPayment $shopOrders) {
                    if ($shopOrders->status == "CANCELLED") {
                        return '<a class="ui teal label">'.__('orders.table.payment_status.cancelled').'</a>';
                    } else if ($shopOrders->status == "PAID") {
                        return '<a class="ui green label">'.__('orders.table.payment_status.paid').'</a>';
                    } else if ($shopOrders->status == "UNPAID") {
                        return '<a class="ui red label">'.__('orders.table.payment_status.unpaid').'</a>';
                    } else if ($shopOrders->status == "REFUNDED") {
                        return '<a class="ui yellow label">'.__('orders.table.payment_status.refunded').'</a>';
                    } else if ($shopOrders->status == "DISPUTED") {
                        return '<a class="ui yellow label">'.__('orders.table.payment_status.disputed').'</a>';
                    }

                    return '<a class="ui blue label">'.__('orders.table.payment_status.await').' <i class="loading cog icon"></i></a>';
                }),
            TD::make('transaction_id', 'Transaction ID')
                ->render(function (\App\Models\OrderPayment $payment) {
                    if ($payment->transaction_id === null) {
                        return '<a><i class="exclamation triangle icon"></i> This payment need to be checked on the provider!</a>';
                    }

                    return "<a href=\"https://provider.com/transactions/" . $payment->transaction_id . "\"> <i class=\"caret square right outline icon\"></i> Check </a>";
                }),
            TD::make('provider')
                ->render(function (\App\Models\OrderPayment $payment) {
                    if ($payment->provider === null) {
                        return 'No provider detected.';
                    }
                    return $payment->provider;
                }),
            TD::make('price')
                ->render(function (\App\Models\OrderPayment $payment) {
                    if ($payment->status == "PAID") {
                        $missing = $this->isMissing($payment);
                        $over = $this->isOverPaid($payment);

                        if ($missing > 0.1) {
                            return '<a class="ui yellow label">Missing ' . $missing . ' Kc</a>';
                        } else if ($over > 0.1) {
                            return '<a class="ui yellow label">Over paid of ' . $over . ' Kc</a>';
                        }
                    }

                    return $payment->price . ' Kc';
                })
        ];
    }

    protected function iconNotFound(): string
    {
        return 'bs.exclamation-triangle';
    }

    protected function textNotFound(): string {
        return '<a class="ui teal label">Awaiting payment... <i class="loading cog icon"></i></a>';
    }

    protected function subNotFound(): string
    {
        return 'No payment has been found yet.';
    }

    protected function calculate(\App\Models\OrderPayment $payment): float
    {
        $orderPrd = OrderedProduct::where('order_id', $payment->order_id)->first();
        $product = ShopProducts::where('id', $orderPrd->product_id)->first();
        $carrier = OrderCarrier::where('order_id', $payment->order_id);

        if ($carrier->exists()) {
            return $product->getNormalizedPrice() + $carrier->first()->price;
        } else {
            return $product->getNormalizedPrice();
        }
    }

    protected function isMissing(\App\Models\OrderPayment $payment): float
    {
        $amount = $this->calculate($payment);

        if ($amount < $payment->price) {
            return $payment->price - $amount;
        }

        return 0;
    }

    protected function isOverPaid(\App\Models\OrderPayment $payment): float
    {
        $amount = $this->calculate($payment);

        if ($amount > $payment->price) {
            return $amount - $payment->price;
        }

        return 0;
    }
}
