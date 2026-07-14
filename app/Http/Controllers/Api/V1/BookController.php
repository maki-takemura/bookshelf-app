<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    /**
     * 書籍一覧API
     */
    public function index(IndexBookRequest $request): AnonymousResourceCollection
    {
        $query = Book::with(['genres'])
            ->withAvg('reviews as average_rating', 'rating')
            ->withCount('reviews');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%")
                    ->orWhere('isbn', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->genre_id);
            });
        }

        $books = $query->paginate(10);

        return BookResource::collection($books);
    }

    /**
     * 書籍登録API
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $user = User::first();
        $validated = $request->validated();
        $validated['user_id'] = $user->id;
        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $book = Book::create($validated);
        $book->genres()->attach($genreIds);
        $book->load('genres');

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 書籍詳細API
     */
    public function show(Book $book): BookResource
    {
        $book->load([
            'genres',
            'reviews.user',
        ]);

        return new BookResource($book);
    }

    /**
     * 書籍更新API
     */
    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $validated = $request->validated();
        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $book->update($validated);
        $book->genres()->sync($genreIds);
        $book->load('genres');

        return new BookResource($book);
    }

    /**
     * 書籍削除API
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(null, 204);
    }
}
