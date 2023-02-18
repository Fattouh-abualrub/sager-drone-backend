<?php

use App\Http\Controllers\API\v1\CategoryController;
use App\Http\Controllers\API\v1\ProductController;
use App\Http\Controllers\API\v1\UserController;
use App\Http\Controllers\API\v1\DashboardController;
use Illuminate\Support\Facades\Route;

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


//User
Route::group(['prefix'=>'/v1'],function (){
    Route::post('/login', [UserController::class,'login']);
    Route::post('/register', [UserController::class,'register']);
    Route::post('/forget-password', [UserController::class,'forget_password']);
});

Route::group(['prefix'=>'/v1','middleware'=>'auth:sanctum'],function (){

    Route::prefix('/users')->group(function () {
        Route::get('/', [
            UserController::class,
            'index'
        ])->name('users.index');  
        Route::delete('/delete/{user}', [
            UserController::class,
            'destroy'
        ])->name('users.delete');   
        Route::get('/show/{user}', [
            UserController::class,
            'show'
        ])->name('users.show');     
        Route::put('/update/{user}', [
            UserController::class,
            'update'
        ])->name('users.update');  
    });

    Route::prefix('/categories')->group(function () {
        Route::get('/', [
            CategoryController::class,
            'index'
        ])->name('categories.index');  
        Route::post('/create', [
            CategoryController::class,
            'create'
        ])->name('categories.create');     
        Route::get('/show/{category}', [
            CategoryController::class,
            'show'
        ])->name('categories.show');     
        Route::put('/update/{category}', [
            CategoryController::class,
            'update'
        ])->name('categories.update');   
        Route::delete('/delete/{category}', [
            CategoryController::class,
            'destroy'
        ])->name('categories.delete'); 
    });

    Route::prefix('/products')->group(function () {
        Route::get('/', [
            ProductController::class,
            'index'
        ])->name('products.index');  
        Route::post('/create', [
            ProductController::class,
            'create'
        ])->name('products.create');     
        Route::get('/show/{product}', [
            ProductController::class,
            'show'
        ])->name('products.show');     
        Route::post('/update/{product}', [
            ProductController::class,
            'update'
        ])->name('products.update');   
        Route::delete('/delete/{product}', [
            ProductController::class,
            'destroy'
        ])->name('products.delete'); 
    });

    Route::post('/logout', [UserController::class,'logout']);
    Route::get('/dashboard', [DashboardController::class,'index']);
});
