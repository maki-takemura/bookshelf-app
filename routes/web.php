<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use Illuminate\Support\Facades\Route;

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

// 仮ルート
// ランキング（仮）
Route::get('/ranking', function () {
    return 'ランキング画面（仮実装）';
})->name('ranking.index');

// お気に入り一覧（仮）
Route::get('/favorites', function () {
    return 'お気に入り一覧（仮実装）';
})->name('favorites.index');
// 仮ルートここまで

Route::middleware('auth')->group(function () {
    Route::resource('genres', GenreController::class);
    Route::resource('books', BookController::class)
        ->except(['index', 'show']);
});

Route::get('/', [BookController::class, 'index'])->name('books.index');
// Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::get('/books/{book}', function ($book) {
    return "書籍詳細画面（仮実装）<br>Book ID: {$book}";
})->name('books.show');
