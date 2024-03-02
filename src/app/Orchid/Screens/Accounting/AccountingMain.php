<?php

namespace app\Orchid\Screens\Accounting;

use App\Models\Accounting;
use App\Models\OrderPayment;
use App\Orchid\Layouts\AccountingTracks;
use App\Orchid\Layouts\Shop\ShopProfit;
use Carbon\Carbon;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class AccountingMain extends Screen
{
    public function query(): iterable
    {
        $lastMonth = Carbon::now();
        $currentYear = Carbon::now()->year;

        return [
            'metrics' => [
                'outstanding_amount' => [
                    'key' => 'outstanding_amount',
                    'value' => number_format(OrderPayment::where('status', 'PAID')
                                ->whereMonth('created_at',$lastMonth)
                                ->whereYear('created_at', $currentYear)
                                ->sum('price') -
                        OrderPayment::where('status', 'REFUNDED')
                            ->whereMonth('created_at',$lastMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('price') -
                        OrderPayment::where('status', 'UNPAID')
                            ->whereMonth('created_at',$lastMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('price') +
                        Accounting::where('type', 'INCOME')
                            ->whereMonth('created_at',$lastMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('amount') -
                        Accounting::where('type', 'EXPENSE')
                            ->whereMonth('created_at',$lastMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('amount')) . ' Kč',
                    'diff' => $this->diff(
                        OrderPayment::where('status', 'PAID')
                                ->whereMonth('created_at', $lastMonth)
                                ->whereYear('created_at', $currentYear)
                                ->sum('price') +
                            OrderPayment::where('status', 'UNPAID')
                                ->whereMonth('created_at', $lastMonth)
                                ->whereYear('created_at', $currentYear)
                                ->sum('price') +
                            OrderPayment::where('status', 'REFUNDED')
                                ->whereMonth('created_at', $lastMonth)
                                ->whereYear('created_at', $currentYear)
                                ->sum('price') +
                            OrderPayment::where('status', 'CANCELLED')
                                ->whereMonth('created_at', $lastMonth)
                                ->whereYear('created_at', $currentYear)
                                ->sum('price'),
                        OrderPayment::all()->sum('price')
                    ),
                    'numeric' => true,
                    'icon' => 'bs.piggy-bank'
                ],
                'overdue_amount' => [
                    'key' => 'overdue_amount',
                    'value' => number_format(OrderPayment::where('status', 'UNPAID')
                            ->whereMonth('created_at',$lastMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('price')) . ' Kč',
                    'diff' => $this->diff(
                        OrderPayment::where('status', 'UNPAID')
                            ->whereMonth('created_at', $lastMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('price'),
                        OrderPayment::where('status', 'UNPAID')->sum('price')
                    ),
                    'numeric' => true,
                    'icon' => 'bs.clock-history'
                ],
                'expenses' => [
                    'key' => 'expenses',
                    'value' => number_format(Accounting::where('type', 'EXPENSE')
                            ->whereMonth('created_at',$lastMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('amount')) . ' Kč',
                    'diff' => $this->diff(
                        Accounting::where('type', 'EXPENSE')
                            ->whereMonth('created_at',$lastMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('amount'),
                        Accounting::where('type', 'EXPENSE')->sum('amount')
                    ),
                    'numeric' => true,
                    'icon' => 'bs.graph-down-arrow'
                ]
            ],
            'income_ratio' => [
                OrderPayment::where('status', 'PAID')->sumByDays('price')->toChart('Příjem z obchodu'),
                Accounting::where('type', 'INCOME')->sumByDays('amount')->toChart('Externí příjem')
            ],
            'external_expense' => [
                OrderPayment::where('status', 'REFUNDED')->sumByDays('price')->toChart('Vrátky z obchodu'),
                OrderPayment::where('status', 'UNPAID')->sumByDays('price')->toChart('Dlužná částka z obchodu'),
                Accounting::where('type', 'EXPENSE')->sumByDays('amount')->toChart("Externí výdaje")
            ],
            'accounting' => Accounting::orderBy('created_at', 'desc')->paginate()
        ];
    }

    public function name(): ?string
    {
        return 'Účetnictví';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.accounting',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Nová operace')
                ->icon('bs.piggy-bank')
                ->href(route('platform.accounting.new')),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Čistý zůstatek' => 'metrics.outstanding_amount',
                'Prodlení' => 'metrics.overdue_amount'
            ]),

            ShopProfit::make('income_ratio' ,'Poměr čistého příjmu')
                ->export(),

            ShopProfit::make('external_expense' ,'Výdaje')
                ->export(),

            AccountingTracks::class
        ];
    }

    public function diff($recent,$previous): float
    {
        if ($recent <= 0 || $previous <= 0)
            return 0.0;

        return (($recent-$previous)/$previous) * 100;
    }
}
