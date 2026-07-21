<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_キーワード検索時255文字だと検索できる(): void
    {
        $keyword = str_repeat('あ', 255);
        $book = Book::factory()->create([
            'title' => $keyword,
        ]);
        Book::factory()->create([
            'title' => '別の書籍',
        ]);

        $response = $this->getJson('/api/v1/books?keyword='.$keyword);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $book->id);
        $response->assertJsonPath('data.0.title', $keyword);
    }

    public function test_キーワード検索時256文字だとバリデーションエラーになる(): void
    {
        $keyword = str_repeat('あ', 256);

        $response = $this->getJson('/api/v1/books?keyword='.$keyword);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('keyword');
    }

    public function test_存在しないジャンルで絞り込みしようとするとバリデーションエラーになる(): void
    {
        $response = $this->getJson('/api/v1/books?genre_id=999999');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('genre_id');
    }

    public function test_登録時タイトルが空だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['title' => '']
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    }

    public function test_登録時タイトルが255文字だと書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $title = str_repeat('あ', 255);

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['title' => $title]
        ));

        $response->assertCreated();
        $response->assertJsonPath('data.title', $title);
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => $title,
            'isbn' => '9781234567891',
        ]);
    }

    public function test_登録時タイトルが256文字だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['title' => str_repeat('あ', 256)]
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    }

    public function test_登録時著者が空だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['author' => '']
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('author');
    }

    public function test_登録時著者が255文字だと書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $author = str_repeat('あ', 255);

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['author' => $author]
        ));

        $response->assertCreated();
        $response->assertJsonPath('data.author', $author);
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'author' => $author,
            'isbn' => '9781234567891',
        ]);
    }

    public function test_登録時著者が256文字だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['author' => str_repeat('あ', 256)]
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('author');
    }

    public function test_登録時_isb_nが空だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['isbn' => '']
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_登録時_isb_nが12桁だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['isbn' => '978123456789']
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_登録時_isb_nが14桁だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['isbn' => '97812345678900']
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_登録時既に使われている_isb_nは登録できない(): void
    {
        $genres = Genre::factory()->count(2)->create();

        Book::factory()->create([
            'isbn' => '9781234567891',
        ]);

        $response = $this->postJson(
            '/api/v1/books',
            $this->validBookData($genres->pluck('id')->all())
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_登録時出版日が空だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['published_date' => '']
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('published_date');
    }

    public function test_登録時出版日が無効な日付だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['published_date' => '2000-02-30']
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('published_date');
    }

    public function test_登録時説明が空でも書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['description' => '']
        ));

        $response->assertCreated();
        $response->assertJsonPath('data.description', null);
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'isbn' => '9781234567891',
            'description' => null,
        ]);
    }

    public function test_登録時画像_ur_lが空でも書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['image_url' => '']
        ));

        $response->assertCreated();
        $response->assertJsonPath('data.image_url', null);
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'isbn' => '9781234567891',
            'image_url' => null,
        ]);
    }

    public function test_登録時画像_ur_lが無効な形式だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['image_url' => 'wrong_image']
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('image_url');
    }

    public function test_登録時画像_ur_lが255文字だと書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $imageUrl = $this->createUrl(255);

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['image_url' => $imageUrl]
        ));

        $response->assertCreated();
        $response->assertJsonPath('data.image_url', $imageUrl);
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'isbn' => '9781234567891',
            'image_url' => $imageUrl,
        ]);
    }

    public function test_登録時画像_ur_lが256文字だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $response = $this->postJson('/api/v1/books', $this->validBookData(
            $genres->pluck('id')->all(),
            ['image_url' => $this->createUrl(256)]
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('image_url');
    }

    public function test_登録時ジャンルが選択されていないとバリデーションエラーになる(): void
    {
        $response = $this->postJson(
            '/api/v1/books',
            $this->validBookData([], ['genres' => ''])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('genres');
    }

    public function test_登録時ジャンルが配列でないとバリデーションエラーになる(): void
    {
        $response = $this->postJson(
            '/api/v1/books',
            $this->validBookData([], ['genres' => 1])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('genres');
    }

    public function test_登録時存在しないジャンルを選択するとバリデーションエラーになる(): void
    {
        $response = $this->postJson(
            '/api/v1/books',
            $this->validBookData([], ['genres' => [999999]])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('genres.0');
    }

    public function test_更新時タイトルが空だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'title' => '',
                'isbn' => $book->isbn,
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    }

    public function test_更新時タイトルが255文字だと書籍を更新できる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();
        $title = str_repeat('あ', 255);

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'title' => $title,
                'isbn' => $book->isbn,
            ])
        );

        $response->assertOk();
        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.title', $title);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $title,
        ]);
    }

    public function test_更新時タイトルが256文字だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'title' => str_repeat('あ', 256),
                'isbn' => $book->isbn,
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    }

    public function test_更新時著者が空だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'author' => '',
                'isbn' => $book->isbn,
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('author');
    }

    public function test_更新時著者が255文字だと書籍を更新できる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();
        $author = str_repeat('あ', 255);

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'author' => $author,
                'isbn' => $book->isbn,
            ])
        );

        $response->assertOk();
        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.author', $author);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'author' => $author,
        ]);
    }

    public function test_更新時著者が256文字だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'author' => str_repeat('あ', 256),
                'isbn' => $book->isbn,
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('author');
    }

    public function test_更新時_isb_nが空だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => '',
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_更新時_isb_nが12桁だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => '978123456789',
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_更新時_isb_nが14桁だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => '97812345678900',
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_更新時更新対象以外に既に使われている_isb_nには更新できない(): void
    {
        $genres = Genre::factory()->count(2)->create();

        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $book = Book::factory()->create([
            'isbn' => '9781234567891',
        ]);

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => '9781234567890',
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_更新時出版日が空だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => $book->isbn,
                'published_date' => '',
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('published_date');
    }

    public function test_更新時出版日が無効な日付だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => $book->isbn,
                'published_date' => '2000-02-30',
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('published_date');
    }

    public function test_更新時説明が空でも書籍を更新できる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $book = Book::factory()->create([
            'description' => '更新前の説明です。',
        ]);

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => $book->isbn,
                'description' => '',
            ])
        );

        $response->assertOk();
        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.description', null);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'description' => null,
        ]);
    }

    public function test_更新時画像_ur_lが空でも書籍を更新できる(): void
    {
        $genres = Genre::factory()->count(2)->create();

        $book = Book::factory()->create([
            'image_url' => 'https://example.com/before.jpg',
        ]);

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => $book->isbn,
                'image_url' => '',
            ])
        );

        $response->assertOk();
        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.image_url', null);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'image_url' => null,
        ]);
    }

    public function test_更新時画像_ur_lが無効な形式だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => $book->isbn,
                'image_url' => 'wrong_url',
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('image_url');
    }

    public function test_更新時画像_ur_lが255文字だと書籍を更新できる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();
        $imageUrl = $this->createUrl(255);

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => $book->isbn,
                'image_url' => $imageUrl,
            ])
        );

        $response->assertOk();
        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.image_url', $imageUrl);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'image_url' => $imageUrl,
        ]);
    }

    public function test_更新時画像_ur_lが256文字だとバリデーションエラーになる(): void
    {
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData($genres->pluck('id')->all(), [
                'isbn' => $book->isbn,
                'image_url' => $this->createUrl(256),
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('image_url');
    }

    public function test_更新時ジャンルが選択されていないとバリデーションエラーになる(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData([], [
                'isbn' => $book->isbn,
                'genres' => '',
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('genres');
    }

    public function test_更新時ジャンルが配列でないとバリデーションエラーになる(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData([], [
                'isbn' => $book->isbn,
                'genres' => 1,
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('genres');
    }

    public function test_更新時存在しないジャンルを選択するとバリデーションエラーになる(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson(
            '/api/v1/books/'.$book->id,
            $this->validBookData([], [
                'isbn' => $book->isbn,
                'genres' => [999999],
            ])
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('genres.0');
    }

    private function validBookData(
        array $genreIds,
        array $overrides = []
    ): array {
        return array_merge([
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genreIds,
        ], $overrides);
    }

    private function createUrl(int $length): string
    {
        $prefix = 'https://example.com/';

        return $prefix.str_repeat('a', $length - strlen($prefix));
    }
}
