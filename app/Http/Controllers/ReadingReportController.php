<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\View\View;

class ReadingReportController extends Controller
{
    /**
     * マイ読書レポート画面を表示
     */
    public function index(): View
    {
        $reviews = Review::with('book.genres')
            ->where('user_id', auth()->id())
            ->get();

        $stats = [
            'summary' => [
                'total_reviews' => $reviews->count(),
                'books_read' => $reviews->unique('book_id')->count(),
                'average_rating' => $reviews->avg('rating') ?? 0,
            ],

            'rating_distribution' => collect(range(1, 5))
                ->map(fn ($rating) => $reviews->where('rating', $rating)->count()),

            'top_rated_books' => $reviews
                ->where('rating', '>=', 4)
                ->sortByDesc('rating')
                ->take(5)
                ->map(fn ($review) => [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ])
                ->values(),

            'genre_ratings' => $reviews
                ->flatMap(fn ($review) => $review->book->genres->map(fn ($genre) => [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'rating' => $review->rating,
                ]))
                ->groupBy('id')
                ->map(fn ($genreReviews) => [
                    'id' => $genreReviews->first()['id'],
                    'name' => $genreReviews->first()['name'],
                    'count' => $genreReviews->count(),
                    'average_rating' => $genreReviews->avg('rating'),
                ])
                ->sortByDesc('average_rating')
                ->take(5)
                ->values(),
        ];

        return view('reports.index', compact('stats'));
    }
}
