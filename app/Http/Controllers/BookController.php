<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookSearchRequest;
use App\Http\Requests\IsbnSearchRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍一覧画面を表示
     */
    public function index(BookSearchRequest $request): View
    {
        $query = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre);
            });
        }

        switch ($request->input('sort', 'latest')) {
            case 'oldest':
                $query->oldest();
                break;

            case 'title':
                $query->orderBy('title');
                break;

            case 'rating':
                $query->orderByDesc('reviews_avg_rating');
                break;

            default:
                $query->latest();
                break;
        }

        $books = $query
            ->paginate(10)
            ->withQueryString();

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録画面を表示する
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * ISBN検索機能
     */
    public function searchByIsbn(IsbnSearchRequest $request): JsonResponse
    {
        $isbn = $request->validated('isbn');

        try {
            $response = Http::get(
                'https://www.googleapis.com/books/v1/volumes',
                [
                    'q' => 'isbn:'.$isbn,
                    'key' => config('services.google_books.api_key'),
                ]
            );

            if (! $response->successful()) {
                return response()->json([
                    'error' => 'API通信エラーが発生しました。',
                ], 500);
            }

            $items = $response->json('items');

            if (empty($items)) {
                return response()->json([
                    'error' => '書籍が見つかりませんでした。',
                ], 404);
            }

            $book = $items[0]['volumeInfo'];

            return response()->json([
                'title' => $book['title'] ?? '',
                'author' => $book['authors'][0] ?? '',
                'description' => $book['description'] ?? '',
                'published_date' => $book['publishedDate'] ?? '',
                'image_url' => $book['imageLinks']['thumbnail'] ?? '',
            ]);
        } catch (\Exception) {
            return response()->json([
                'error' => 'API通信エラーが発生しました。',
            ], 500);
        }
    }

    /**
     * 書籍を登録
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $book = DB::transaction(function () use ($validated, $genreIds) {
            $book = Auth::user()->books()->create($validated);
            $book->genres()->attach($genreIds);

            return $book;
        });

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍詳細画面を表示
     */
    public function show(Book $book): View
    {
        $book->load([
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        ]);

        Auth::user()?->load(['favoriteBooks', 'likedReviews']);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集画面を表示
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍情報を更新
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validated();
        $genreIds = $validated['genres'];
        unset($validated['genres']);

        DB::transaction(function () use ($book, $validated, $genreIds): void {
            $book->update($validated);
            $book->genres()->sync($genreIds);
        });

        return redirect()->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    /**
     * 書籍を削除
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}
