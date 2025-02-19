<?php

namespace app\Orchid\Screens\Accounting;

use App\Models\TransactionsReport;
use App\Orchid\Layouts\AccountingTRSReportLayout;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpFoundation\Request;

class AccountingTRSReport extends Screen
{

    public function query(): iterable
    {
        return [
            'transactions' => TransactionsReport::paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Transaction Monthly Report';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Refresh')
                ->icon('bs.arrow-repeat')
                ->method('refresh'),

            Button::make('Force Generate')
                ->icon('bs.journal-plus')
                ->type(Color::PRIMARY)
                ->download(true)
                ->method('force')
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
            AccountingTRSReportLayout::class
        ];
    }

    public function refresh()
    {
        Toast::success('You refreshed the page.')
            ->autoHide();

        return redirect()->route('platform.accounting.transactions.reports');
    }

    public function force()
    {
        Artisan::queue('app:generate-transactions-report', []);

        Toast::success('You generated a new monthly report.')
            ->autoHide();

        return redirect()->route('platform.accounting.transactions.reports');
    }

    public function delete(Request $request)
    {
        $report = TransactionsReport::where('report_id', $request->get('reportId'));
        if ($report->exists()) {
            $data = $report->first();
            $data->delete();

            Storage::disk('public')->delete($data->attachment_id);

            Toast::error('You deleted ' . $request->get('reportId'))
                ->autoHide();
        } else {
            Toast::error('This reportId does not exists.')
                ->autoHide();
        }
        return redirect()->route('platform.accounting.transactions.reports');
    }
}
