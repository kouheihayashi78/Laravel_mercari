<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyPage\ProfileController;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('top');

Route::prefix('mypage')->namespace('MyPage')->middleware('auth')->group(function(){//prefix()はグループ化, namespaceメソッドでコントローラの名前空間の接頭辞を指定
    Route::get('edit-profile', 'ProfileController@showProfileEdit')->name('mypage.edit-profile');
});
