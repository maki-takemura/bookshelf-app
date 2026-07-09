<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * レビューを投稿
     */
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        $validated = $request->validated();

        $validated['user_id'] = Auth::id();
        $validated['book_id'] = $book->id;

        Review::create($validated);

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを投稿しました。');
    }

    /**
     * 投稿者本人のみレビュー編集画面を表示
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * 投稿者本人のみレビューを更新
     */
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $book = $review->book;

        $review->update($request->validated());

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを更新しました。');
    }

    /**
     * 投稿者本人のみレビューを削除
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $book = $review->book;

        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを削除しました。');
    }
}
