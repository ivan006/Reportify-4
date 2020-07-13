<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/dropbox', "report_c@state");
Route::any('/update_cache', "report_c@update_updates_pending_log");
// https://red.bluegemify.co.za/update_updates_pending_log?test=123
