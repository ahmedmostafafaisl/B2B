<?php

use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\UserStockController;

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
})->name('home');


Route::get('/payment-success', function () {
    $status = 'failed';
    $payment_type = 'tamara';
    // Get from query string
    return view('Payment.result', compact('status', 'payment_type'));
})->name('payment.success');


Route::get('user-stock/export-all', [UserStockController::class, 'exportAllUsersStock']);
