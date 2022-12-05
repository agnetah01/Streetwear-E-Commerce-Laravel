<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;






Route::middleware(['auth','isAdmin'])->prefix('admin')->as('admin.')->group(function () {
    
    Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
    

    Route::get('categories',[CategoryController::class,'index'])->name('category.index');
    
    Route::controller(ProductController::class)->as('product.')->prefix('products')->group(function () {
        
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{product:slug}/edit', 'edit')->name('edit');

    });
    
    Route::get('colors',[ColorController::class,'index'])->name('color.index');


    Route::get('customers',[CustomerController::class,'index'])->name('customer.index');
    
});