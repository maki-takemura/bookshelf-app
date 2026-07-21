<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ジャンル情報_平均評価_レビュー件数を含めた書籍一覧を_jso_n形式で取得できる(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
        ]);
        $book->genres()->attach($genre);

        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 4,
        ]);
        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'genres' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                    'average_rating',
                    'reviews_count',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
        $response->assertJsonPath('data.0.id', $book->id);
        $response->assertJsonPath('data.0.title', 'テスト書籍');
        $response->assertJsonPath('data.0.genres.0.id', $genre->id);
        $response->assertJsonPath('data.0.genres.0.name', '小説');
        $response->assertJsonPath('data.0.average_rating', 4.5);
        $response->assertJsonPath('data.0.reviews_count', 2);
    }

    public function test_書籍一覧でキーワード検索ができる(): void
    {
        $targetBook = Book::factory()->create([
            'title' => '吾輩は猫である',
        ]);
        Book::factory()->create([
            'title' => '坊っちゃん',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=猫');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $targetBook->id);
        $response->assertJsonPath('data.0.title', '吾輩は猫である');
    }

    public function test_書籍一覧でジャンルでの絞り込みができる(): void
    {
        $targetGenre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        $targetBook = Book::factory()->create();
        $otherBook = Book::factory()->create();

        $targetBook->genres()->attach($targetGenre);
        $otherBook->genres()->attach($otherGenre);

        $response = $this->getJson(
            '/api/v1/books?genre_id='.$targetGenre->id
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $targetBook->id);
        $response->assertJsonPath(
            'data.0.genres.0.id',
            $targetGenre->id
        );
    }

    public function test_書籍一覧のページネーションが機能している(): void
    {
        Book::factory()->count(11)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('meta.total', 11);
        $response->assertJsonPath('meta.last_page', 2);
    }

    public function test_ジャンル情報とレビュー情報を含めた書籍詳細を_jso_n形式で取得できる(): void
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $book = Book::factory()->create([
            'title' => 'テスト書籍',
        ]);
        $book->genres()->attach($genre);

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストレビュー',
        ]);

        $response = $this->getJson('/api/v1/books/'.$book->id);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'reviews' => [
                    '*' => [
                        'id',
                        'user_name',
                        'rating',
                        'comment',
                        'reviewed_date',
                    ],
                ],
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.title', 'テスト書籍');
        $response->assertJsonPath('data.genres.0.id', $genre->id);
        $response->assertJsonPath('data.genres.0.name', '小説');
        $response->assertJsonPath('data.reviews.0.id', $review->id);
        $response->assertJsonPath(
            'data.reviews.0.user_name',
            'テストユーザー'
        );
        $response->assertJsonPath('data.reviews.0.rating', 5);
        $response->assertJsonPath(
            'data.reviews.0.comment',
            'テストレビュー'
        );
    }

    public function test_存在しない_i_dの書籍詳細を取得しようとすると404エラーになる(): void
    {
        $response = $this->getJson('/api/v1/books/999999');

        $response->assertNotFound();
    }

    public function test_正しい情報で書籍登録でき、201が返る(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $bookData = [
            'title' => 'API登録テスト',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-07-21',
            'description' => 'API登録テストの説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => $genres->pluck('id')->all(),
        ];

        $response = $this->postJson('/api/v1/books', $bookData);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonPath('data.title', 'API登録テスト');
        $response->assertJsonPath('data.author', 'テスト著者');
        $response->assertJsonPath('data.isbn', '9781234567890');
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'API登録テスト',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
        ]);
        $book = Book::where('isbn', '9781234567890')->firstOrFail();
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    public function test_正しい情報で書籍情報の更新ができ、200が返る(): void
    {
        $oldGenre = Genre::factory()->create();
        $newGenres = Genre::factory()->count(2)->create();

        $book = Book::factory()->create([
            'title' => '更新前タイトル',
            'isbn' => '9781234567890',
        ]);
        $book->genres()->attach($oldGenre);

        $bookData = [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-07-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/updated-book.jpg',
            'genres' => $newGenres->pluck('id')->all(),
        ];

        $response = $this->putJson('/api/v1/books/'.$book->id, $bookData);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.title', '更新後タイトル');
        $response->assertJsonPath('data.author', '更新後著者');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9781234567890',
        ]);
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);
        foreach ($newGenres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    public function test_存在しない_i_dの書籍情報を更新しようとすると404エラーになる(): void
    {
        $genre = Genre::factory()->create();
        $bookData = [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-07-21',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/updated-book.jpg',
            'genres' => [$genre->id],
        ];

        $response = $this->putJson('/api/v1/books/999999', $bookData);

        $response->assertNotFound();
    }

    public function test_書籍の削除ができ、204が返る(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre);
        Review::factory()->create(['book_id' => $book->id]);
        $user->favoriteBooks()->attach($book);

        $response = $this->deleteJson('/api/v1/books/'.$book->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
        $this->assertDatabaseMissing('reviews', ['book_id' => $book->id]);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_存在しない_i_dの書籍を削除しようとすると404エラーになる(): void
    {
        $response = $this->deleteJson('/api/v1/books/999999');

        $response->assertNotFound();
    }
}
