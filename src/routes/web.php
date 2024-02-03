<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PagesController;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Orchid\Platform\Http\Controllers\LoginController;

use Orchid\Platform\Http\Controllers\AsyncController;
use Orchid\Platform\Http\Controllers\AttachmentController;
use Orchid\Platform\Http\Controllers\IndexController;
use Orchid\Platform\Http\Screens\NotificationScreen;
use Orchid\Platform\Http\Screens\SearchScreen;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes...
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::middleware('throttle:60,1')
    ->post('login', [LoginController::class, 'login'])
    ->name('login.auth');

Route::get('lock', [LoginController::class, 'resetCookieLockMe'])->name('login.lock');
Route::get('switch-logout', [LoginController::class, 'switchLogout']);
Route::post('switch-logout', [LoginController::class, 'switchLogout'])->name('switch.logout');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/pages/{slug}', [PagesController::class, 'index']);

Route::prefix('dashboard')->group(function () {
    Route::get('/', [IndexController::class, 'index'])
        ->name('index')
        ->breadcrumbs(fn (Trail $trail) => $trail->push(__('Home'), route('index')));

    Route::screen('search/{query}', SearchScreen::class)
        ->name('search')
        ->breadcrumbs(fn (Trail $trail, string $query) => $trail->parent('index')
            ->push(__('Search'))
            ->push($query));
});

Route::post('async/{screen}/{method?}/{template?}', [AsyncController::class, 'load'])
->name('async');

Route::post('listener/{screen}/{layout}', [AsyncController::class, 'listener'])
->name('async.listener');

Route::prefix('systems')->group(function () {
    Route::post('uploaded', [AttachmentController::class, 'uploaded'])
        ->name('systems.files.uploaded');
});


Route::post('relation', [\Orchid\Platform\Http\Controllers\RelationController::class, 'view'])
    ->name('platform.systems.relation');


Route::screen('notifications/{id?}', NotificationScreen::class)
->name('notifications')
->breadcrumbs(fn (Trail $trail) => $trail->parent('index')
    ->push(__('Notifications')));

Route::post('api/notifications', [NotificationScreen::class, 'unreadNotification'])
->name('api.notifications');

Route::get('/health', function (Request $request) {
    return [
        'status' => "ok"
    ];
})->name("health");

Route::get('/build', function (Request $request) {
    return [
        'version' => file_get_contents('../VERSION'),
        'rev' => env('GIT_COMMIT')
    ];
})->name("build");

Route::get('/report', function (\Illuminate\Http\Request $request) {
    $reportId = $request->query('reportId');

    if ($reportId != null) {
       $storage = \Illuminate\Support\Facades\Storage::disk('public');

       $report = \App\Models\ShopReports::where('report_id', $reportId);

       if ($report->exists()) {
           $data = $report->firstOrFail();
           if ($storage->exists($data->attachment_id)) {
               return response()->download(storage_path('app/public/' . $data->attachment_id));
           } else {
               return response()->json([
                   'error' => 'Not found in the storage.'
               ]);
           }
       } else {
           return response()->json([
               'error' => 'No records in database for ' .  $reportId
           ]);
       }
    } else {
        return response()->json([
            'error' => 'The reportId cannot be null.'
        ]);
    }
})->middleware('auth')->name('api.shop.report');

Route::get('/voucher', function (\Illuminate\Http\Request $request) {
    $voucherCode = $request->query('voucherCode');
    $storage = \Illuminate\Support\Facades\Storage::disk('public');

    if ($voucherCode != null) {
        $voucher = \App\Models\ShopVouchers::where('code', $voucherCode);

        if ($voucher->exists()) {
            $voucherData = $voucher->first();

            if ($storage->exists('default_voucher_card.png')) {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($storage->get('default_voucher_card.png'));

                $image->text($voucherData->code, 692,486, function ($font) {
                    $font->filename(public_path('fonts/source.ttf'));
                    $font->color('#FFFFFF');
                    $font->size(30);
                    $font->align('center');
                    $font->valign('middle');
                    $font->lineHeight(1.6);
                    $font->angle(10);
                });

                $qrCodes = QrCode::size(120)
                    ->merge(public_path('img/fluffici.png'), .4)
                    ->color(255, 0, 46)
                    ->style('square')
                    ->generate('https://dashboard.fluffici.eu/check/voucher/' . $voucherData->code);

                $image->place(
                    $qrCodes,
                    'bottom-right',
                    780,
                    965,
                    90
                );

                $path = storage_path('/app/public/voucher-' . $voucherCode . '.png');
                $image->save($path)->toPng();

                return response()->download($path);
            } else {
                return response()->json([
                    'error' => 'No default voucher card.'
                ]);
            }
        } else {
            return response()->json([
                'error' => 'This voucher code is invalid.'
            ]);
        }
    } else {
        return response()->json([
            'error' => 'This voucher code is invalid.'
        ]);
    }
})->middleware('auth')->name('api.shop.voucher');

Route::get('/api/order', function (\Illuminate\Http\Request $request) {
    $orderId = $request->query('orderId');
    if ($orderId == null) {
        return response()->json([
            'status' => false,
            'error' => 'MISSING_ID',
            'message' => 'No order ID was found.'
        ]);
    }

    $order = \App\Models\ShopOrders::where('order_id', $orderId);
    $payment = \App\Models\OrderPayment::where('order_id', $orderId);
    $products = \App\Models\OrderedProduct::where('order_id', $orderId);


    if ($order->exists()) {
        $orderData = $order->first();

        if ($orderData->status == "COMPLETED"
            || $orderData->status == "DELIVERED"
            || $orderData->status == "ARCHIVED") {
            return response()->json([
                'status' => false,
                'error' => 'ORDER_ALREADY_PROCESSED',
                'message' => 'This order has been already processed since ' . \Carbon\Carbon::parse($orderData->updated_at)->diffForHumans() . '.'
            ]);
        }

        $data = [];
        $data['order'] = $order->first();

        if ($payment->exists()) {
            $data['payment'] = $payment->first();
        } else {
            $data['payment'] = false;
        }

        if ($products->exists()) {
            $data['product'] = $products->first();
        } else {
            $data['product'] = false;
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    } else {
        return response()->json([
            'status' => false,
            'error' => 'ORDER_NOT_FOUND',
            'message' => 'This order does not exists in our records.'
        ]);
    }

});
