<?php

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
Route::get('/', function () {
    return view('welcome');
})->name('books.index');

Route::get('/books/{book}', function ($book) {
    return "書籍詳細画面（仮実装）<br>Book ID: {$book}";
})->name('books.show');

// ランキング（仮）
Route::get('/ranking', function () {
    return 'ランキング画面（仮実装）';
})->name('ranking.index');

// お気に入り一覧（仮）
Route::get('/favorites', function () {
    return 'お気に入り一覧（仮実装）';
})->name('favorites.index');

// 書籍登録（仮）
Route::get('/books/create', function () {
    return '書籍登録画面（仮実装）';
})->name('books.create');
// 仮ルートここまで

Route::middleware('auth')->group(function () {
    Route::resource('genres', GenreController::class);
});
