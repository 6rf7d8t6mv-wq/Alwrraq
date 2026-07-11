<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\FileUploadController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', function () {
    $students = [
        ['name' => 'أحمد', 'subject' => 'رياضيات', 'grade' => 95],
        ['name' => 'سارة', 'subject' => 'علوم', 'grade' => 88],
        ['name' => 'محمد', 'subject' => 'إنجليزي', 'grade' => 78],
        ['name' => 'ليلى', 'subject' => 'تاريخ', 'grade' => 92],
        ['name' => 'يوسف', 'subject' => 'فيزياء', 'grade' => 84],
    ];

    return view('grades', compact('students'));
})->middleware('auth')->name('home');

Route::post('/upload-file', [FileUploadController::class, 'upload'])->middleware('auth');
Route::patch('/order-files/{file}', [FileUploadController::class, 'updateFile'])->middleware('auth');
Route::delete('/order-files/{file}', [FileUploadController::class, 'destroyFile'])->middleware('auth');

Route::middleware('auth')->prefix('cart')->name('cart.')->group(function () {
    Route::get('/{order}', [CartController::class, 'show'])->name('show');
    Route::post('/{order}/pay', [CartController::class, 'pay'])->name('pay');
});

Route::get('/my-orders', [CustomerOrderController::class, 'index'])
    ->middleware('auth')
    ->name('orders.index');
Route::delete('/my-orders/{order}', [CustomerOrderController::class, 'destroy'])
    ->middleware('auth')
    ->name('orders.destroy');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/settings', [AccountController::class, 'edit'])->name('settings');
    Route::patch('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/address', [AccountController::class, 'updateAddress'])->name('address.update');
    Route::patch('/password', [AccountController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::patch('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/files/{file}/download', [AdminController::class, 'download'])->name('files.download');
});
