<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * 書籍のお気に入り登録機能
     */
    public function toggle(Book $book): RedirectResponse
    {
        $book->favoritedByUsers()->toggle(Auth::id());

        return back();
    }

    /**
     * ユーザーごとの書籍のお気に入り一覧画面を表示
     */
    public function index(): View
    {
        $books = Auth::user()->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
