<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/classes', function () {
    return view('index'); 
})->name('classes.index');