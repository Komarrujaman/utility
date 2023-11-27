<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FirstUplinkController;
use App\Http\Controllers\lastUplinkController;
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

Route::get('/', [Controller::class, 'index']);

Route::get('device', [DeviceController::class, 'index'])->name('get-device');
Route::get('first', [FirstUplinkController::class, 'index'])->name('get-first-uplink');
Route::get('last', [lastUplinkController::class, 'index'])->name('get-last-uplink');
