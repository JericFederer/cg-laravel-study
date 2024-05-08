<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookController;

// * Route to display the home page now displays the book list view as well
Route::get('/',[BookController::class,'renderListView'])->name('home');

// * Route for displaying the book list view
Route::get('/books',[BookController::class,'renderListView'])->name('books.renderListView');
Route::get('/books/sortedbytitle',[BookController::class,'renderTitleSortedListView'])->name('books.renderTitleSortedListView');
Route::get('/books/sortedbyauthor',[BookController::class,'renderAuthorSortedListView'])->name('books.renderAuthorSortedListView');

// * Route for displaying the create new book view and saving the new book record to DB
Route::get('/books/create',[BookController::class,'renderCreateView'])->name('books.renderCreateView');
Route::post('/books',[BookController::class,'createBook'])->name('books.createBook');

// * Route for displaying the update book view and saving the changes to DB
Route::get('/books/update/{id}',[BookController::class,'renderUpdateView'])->name('books.renderUpdateView');
Route::post('/books/update/{id}',[BookController::class,'updateBook'])->name('books.updateBook');

// * Route for deleting a book record
Route::delete('/books',[BookController::class,'deleteBook'])->name('books.deleteBook');

// * Route for generating CSV and XML files
Route::post('/books/generateCSVAndXML',[BookController::class,'generateCSVAndXML'])->name('download.zip');

