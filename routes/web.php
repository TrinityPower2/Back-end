<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
    return view('welcome');
});

Route::get('/ping/{user}', function (string $user) {
    return ['message' => "$user"];
});

Route::get('/auth/login', function (Request $request) {
    if($request->input('email') == "Antoine" && $request->input('password')== "password"){
        return ['message' => "authenticated"];
    }
    else{
        return ['message' => "invalid",'email' => $request->input('email'), 'password' => $request->input('password')];
    }
});
