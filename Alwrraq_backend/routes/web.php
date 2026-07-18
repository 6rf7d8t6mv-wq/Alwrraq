<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminStationeryProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\EducationalInstitutionController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\StationeryController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::post('/admin', [AuthController::class, 'adminLogin'])->name('admin.login.store');
});

Route::get('/educational-institutions', [EducationalInstitutionController::class, 'index'])
    ->name('educational-institutions.index');

Route::post('/language', function (Request $request) {
    $data = $request->validate([
        'locale' => ['required', 'in:ar,en'],
    ]);

    session(['ui_locale' => $data['locale']]);

    return back();
})->name('language.switch');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('chat')->name('chat.')->group(function () {
    Route::get('/conversations', [ChatController::class, 'conversations'])->name('conversations');
    Route::get('/conversations/{conversation}', [ChatController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'store'])->name('messages.store');
});

Route::get('/', function () {
    return view('public.home');
})->name('public.home');

Route::get('/home', function (Request $request) {
    $students = [
        ['name' => 'أحمد', 'subject' => 'رياضيات', 'grade' => 95],
        ['name' => 'سارة', 'subject' => 'علوم', 'grade' => 88],
        ['name' => 'محمد', 'subject' => 'إنجليزي', 'grade' => 78],
        ['name' => 'ليلى', 'subject' => 'تاريخ', 'grade' => 92],
        ['name' => 'يوسف', 'subject' => 'فيزياء', 'grade' => 84],
    ];

    $editOrderPayload = null;
    if ($request->filled('order')) {
        $formatSize = function (int $bytes): string {
            if ($bytes <= 0) {
                return '0 Bytes';
            }

            $units = ['Bytes', 'KB', 'MB', 'GB'];
            $index = min((int) floor(log($bytes, 1024)), count($units) - 1);

            return round($bytes / (1024 ** $index), 2).' '.$units[$index];
        };

        $editOrder = Order::query()
            ->where('user_id', auth()->id())
            ->where('payment_status', '!=', 'paid')
            ->with('files')
            ->find($request->integer('order'));

        if ($editOrder) {
            $editOrderPayload = [
                'id' => $editOrder->id,
                'service_type' => $editOrder->service_type,
                'files' => $editOrder->files->map(fn ($file) => [
                    'id' => $file->id,
                    'file_type' => $file->file_type,
                    'filename' => $file->original_name,
                    'pages' => $file->pages,
                    'size' => $formatSize((int) $file->size),
                    'binding_type' => $file->binding_type,
                    'copies' => $file->copies,
                    'print_sides' => $file->print_sides,
                    'page_size' => $file->page_size,
                    'paper_color' => $file->paper_color,
                    'thesis_project_type' => $file->thesis_project_type,
                    'university_name' => $file->university_name,
                    'cover_color' => $file->cover_color,
                    'writing_color' => $file->writing_color,
                    'cd_type' => $file->cd_type,
                    'cd_copies' => $file->cd_copies,
                    'cd_price' => $file->cd_price,
                    'research_title' => $file->research_title,
                    'research_student_name' => $file->research_student_name,
                    'research_instructor_name' => $file->research_instructor_name,
                    'print_price' => $file->print_price,
                    'binding_price' => $file->binding_price,
                    'total_price' => $file->total_price,
                ])->values(),
            ];
        }
    }

    return view('grades', compact('students', 'editOrderPayload'));
})->middleware('auth')->name('home');

Route::post('/upload-file', [FileUploadController::class, 'upload'])->middleware('auth');
Route::post('/research-order', [FileUploadController::class, 'saveResearchOrder'])->middleware('auth');
Route::patch('/order-files/{file}', [FileUploadController::class, 'updateFile'])->middleware('auth');
Route::delete('/order-files/{file}', [FileUploadController::class, 'destroyFile'])->middleware('auth');

Route::middleware('auth')->prefix('stationery')->name('stationery.')->group(function () {
    Route::get('/', [StationeryController::class, 'index'])->name('index');
    Route::post('/products/{product}/add', [StationeryController::class, 'add'])->name('products.add');
    Route::delete('/products/{product}/remove', [StationeryController::class, 'remove'])->name('products.remove');
    Route::delete('/items/{item}', [StationeryController::class, 'removeItem'])->name('items.destroy');
});

Route::middleware('auth')->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'showAll'])->name('index');
    Route::get('/payment', [CartController::class, 'payment'])->name('payment');
    Route::post('/pay', [CartController::class, 'payAll'])->name('pay-all');
    Route::get('/{order}', [CartController::class, 'show'])->name('show');
    Route::patch('/{order}/delivery', [CartController::class, 'updateDelivery'])->name('delivery.update');
    Route::patch('/{order}/discount', [CartController::class, 'applyDiscount'])->name('discount.apply');
    Route::post('/{order}/pay', [CartController::class, 'pay'])->name('pay');
});

Route::get('/my-orders', [CustomerOrderController::class, 'index'])
    ->middleware('auth')
    ->name('orders.index');
Route::delete('/my-orders/{order}', [CustomerOrderController::class, 'destroy'])
    ->middleware('auth')
    ->name('orders.destroy');
Route::get('/my-orders/{order}/files/{file}', [CustomerOrderController::class, 'viewUploadedFile'])
    ->middleware('auth')
    ->name('orders.file.view');
Route::get('/my-orders/{order}/delivered-files/{deliveredFile}', [CustomerOrderController::class, 'downloadDeliveredFile'])
    ->middleware('auth')
    ->name('orders.delivered-file');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/settings', [AccountController::class, 'edit'])->name('settings');
    Route::patch('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/address', [AccountController::class, 'updateAddress'])->name('address.update');
    Route::patch('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [AccountController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/admin', [AuthController::class, 'showAdminLogin'])->name('admin.dashboard');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/stationery-products', [AdminStationeryProductController::class, 'index'])->name('stationery-products.index');
    Route::post('/stationery-products', [AdminStationeryProductController::class, 'store'])->name('stationery-products.store');
    Route::patch('/stationery-products/{product}', [AdminStationeryProductController::class, 'update'])->name('stationery-products.update');
    Route::delete('/stationery-products/{product}', [AdminStationeryProductController::class, 'destroy'])->name('stationery-products.destroy');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/live-status', [AdminController::class, 'liveStatus'])->name('live-status');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/discount-codes', [AdminController::class, 'storeDiscountCode'])->name('discount-codes.store');
    Route::delete('/discount-codes/{discountCode}', [AdminController::class, 'destroyDiscountCode'])->name('discount-codes.destroy');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/email', [AdminController::class, 'updateUserEmail'])->name('users.email.update');
    Route::patch('/users/{user}/permissions', [AdminController::class, 'updateUserPermissions'])->name('users.permissions.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::patch('/orders/{order}/open', [AdminController::class, 'openOrder'])->name('orders.open');
    Route::patch('/orders/{order}/discount', [AdminController::class, 'applyOrderDiscount'])->name('orders.discount.apply');
    Route::delete('/orders/{order}', [AdminController::class, 'destroyOrder'])->name('orders.destroy');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::patch('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/orders/{order}/delivered-file', [AdminController::class, 'uploadDeliveredFile'])->name('orders.delivered-file.upload');
    Route::get('/delivered-files/{deliveredFile}/download', [AdminController::class, 'downloadDeliveredFile'])->name('delivered-files.download');
    Route::delete('/delivered-files/{deliveredFile}', [AdminController::class, 'destroyDeliveredFile'])->name('delivered-files.destroy');
    Route::get('/files/{file}/view', [AdminController::class, 'viewFile'])->name('files.view');
    Route::get('/files/{file}/download', [AdminController::class, 'download'])->name('files.download');
});
