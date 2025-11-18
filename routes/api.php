<?php

use App\Http\Controllers\Api\TaskController;
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

Route::middleware('auth:sanctum')->group(function () {
    // Task CRUD endpoints
    Route::apiResource('tasks', TaskController::class);

    // Additional task endpoints could go here
    // Route::get('tasks/{task}/comments', [TaskController::class, 'comments']);
    // Route::post('tasks/{task}/assign', [TaskController::class, 'assign']);
});
