<?php

use Illuminate\Support\Facades\Route;
// Make sure this line correctly points to your QuoteController
use App\Http\Controllers\QuoteController;

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

// This route tells Laravel:
// When a GET request comes to the root URL ('/'),
// execute the 'showQuote' method within the 'QuoteController' class.
// The 'name' is optional but good practice for generating URLs later.
Route::get('/', [QuoteController::class, 'showQuote'])->name('quote.show');

// This route handles the AJAX request from your UI to get a new quote.
Route::get('/get-new-quote', [QuoteController::class, 'getNewQuote'])->name('quote.new');

