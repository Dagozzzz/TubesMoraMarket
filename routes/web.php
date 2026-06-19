<?php

use App\Http\Controllers\KasirController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('kasir')->name('kasir.')->controller(KasirController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'authenticate')->name('login.store');
    Route::post('/logout', 'logout')->name('logout');

    Route::get('/dashboard', 'dashboard')->name('dashboard');

    Route::post('/cart', 'addToCart')->name('cart.store');
    Route::patch('/cart/{barang}', 'updateCart')->name('cart.update');
    Route::delete('/cart/{barang}', 'removeFromCart')->name('cart.destroy');

    Route::get('/checkout', 'checkout')->name('checkout');
    Route::post('/checkout', 'pay')->name('checkout.pay');
    Route::post('/checkout/finish', 'finishPayment')->name('checkout.finish');
});
