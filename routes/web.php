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

// Option 1: Direct View Return (Simplest for a static page)
// If you just want to display a view without any controller logic on initial load,
// you can do it directly like this.
// Route::get('/', function () {
//     // This will load 'resources/views/welcome.blade.php'
//     // You could change 'welcome' to 'ui' if you want to directly load ui.blade.php
//     // return view('welcome');
//     // return view('ui'); // If you want to load ui.blade.php directly
// });


// Option 2: Route to Controller Method (Recommended for your app)
// This is the approach we've been using and is best practice
// because it allows you to prepare data (like the initial quote)
// in the controller before passing it to the view.
Route::get('/', [QuoteController::class, 'showQuote'])->name('quote.show');


// This route handles the AJAX request from your UI to get a new quote.
// It's separate from the initial page load.
Route::get('/get-new-quote', [QuoteController::class, 'getNewQuote'])->name('quote.new');

// NEW: Route for handling POST requests
// This route would typically be used when a form in your UI (e.g., in ui.blade.php)
// submits data using the POST method. You would then create a new method
// in your QuoteController (e.g., 'submitQuote') to handle the incoming data.
Route::post('/submit-quote', [QuoteController::class, 'submitQuote'])->name('quote.submit');

