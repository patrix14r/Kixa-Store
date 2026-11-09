<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('cliente.home');
});

Route::get('/admin', function () {
    return view('admin.home');
});