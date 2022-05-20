<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RentalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/customer', [RentalController::class, 'createCustomer']);
Route::post('/vehicle', [RentalController::class, 'createVehicle']);
Route::post('/rental', [RentalController::class, 'createRental']);
Route::get('/returnVehicle', [RentalController::class, 'getReturnVehicleInfo']);
Route::get('/getCustomer',[RentalController::class, 'getCustomerData']);
Route::get('/vehicle',[RentalController::class, 'getVehicles']);
Route::get('/customerId',[RentalController::class, 'getCustomerId']);
Route::get('/availableVehicles',[RentalController::class, 'getAvailableVehicles']);
Route::put('payment', [RentalController::class, 'updatePaymentStatus']);
