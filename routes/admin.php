<?php

Route::middleware(['auth', 'verified'])->namespace('Admin')->group(function (){

    Route::get('/', 'DashboardController@index');

    Route::resource('/dashboard', 'DashboardController', ['only' => 'index']);
    Route::resource('/expense', 'ExpenseController', ['except' => 'show']);
    Route::resource('/account', 'AccountController', ['except' => 'show']);
    Route::resource('/user', 'UserController', ['except' => 'show']);
});
