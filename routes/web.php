<?php

use App\Http\Controllers\TaskController;
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
    return redirect()->route('login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/tasks', [TaskController::class, 'index'])->name('task.index');
    Route::get('/tasks/list/ajax', [TaskController::class, 'list'])->name('task.list');
    Route::post('/tasks', [TaskController::class, 'store'])->name('task.store');
    Route::get('/tasks/show/{id}', [TaskController::class, 'show'])->name('task.show');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('task.update');
    Route::delete('/{task_id}', [TaskController::class, 'destroy'])->name('task.destroy');

    Route::post('ajax/reorder', [TaskController::class, 'reorder'])->name('task.reorder');
});
