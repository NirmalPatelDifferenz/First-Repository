<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {return view('auth.login');});
Route::any('/logout',[LoginController::class,'logout'])->name('logout');

Auth::routes();
Route::get('/register',function(){return view('auth.login');})->name('register');
Route::group(['middleware'=>['auth','PreventBackHistory']],function(){
    Route::get('/home', [HomeController::class, 'index'])->name('dashboard');

    Route::get('/product',[ProductController::class,'index'])->name('product');
    Route::get('/product/getProduct',[ProductController::class,'getProductData'])->name('product.getData');
    Route::post('/product',[ProductController::class,'storeProduct'])->name('product.store');
    Route::post('product/delete',[ProductController::class,'deleteProduct'])->name('product.delete');
});
