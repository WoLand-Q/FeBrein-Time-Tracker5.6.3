<?php

use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserTimeLogController;
use App\Http\Controllers\Admin\UserWorkDayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

require __DIR__.'/auth.php';

    Route::middleware(['auth:sanctum'])
        ->prefix('admin')
        ->group(function () {
            Route::get('user', function (Request $request) {
                return $request->user();
            })->name('home');

            Route::middleware(['role:Admin'])->group(function () {
                Route::apiResource('groups',GroupController::class);
                Route::apiResource('users',UserController::class);
                Route::apiResource('userTimeLogs',UserTimeLogController::class);
                Route::apiResource('userWorkDays',UserWorkDayController::class);
            });

        });

Route::middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('user', function (Request $request) {
            return $request->user();
        })->name('home');

    Route::middleware(['role:Admin,User'])->group(function () {
        Route::get('groups', [GroupController::class, 'index']);
        Route::get('users', [UserController::class, 'index']);
        Route::get('userTimeLogs', [UserTimeLogController::class, 'index']);
        Route::post('userTimeLogs', [UserTimeLogController::class, 'store']);
        Route::get('userWorkDays', [UserWorkDayController::class, 'index']);

    });

    });
