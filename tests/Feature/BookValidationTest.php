<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_登録時タイトルが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => '',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_登録時タイトルが255文字だと書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => str_repeat('あ', 255),
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $book = Book::where('isbn', '9781234567891')->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => str_repeat('あ', 255),
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);
    }

    public function test_登録時タイトルが256文字だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => str_repeat('あ', 256),
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_登録時著者が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('author');
    }

    public function test_登録時著者が255文字だと書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => str_repeat('あ', 255),
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $book = Book::where('isbn', '9781234567891')->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => str_repeat('あ', 255),
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);
    }

    public function test_登録時著者が256文字だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => str_repeat('あ', 256),
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('author');
    }

    public function test_登録時_isb_nが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_登録時_isb_nが12桁だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '978123456789',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_登録時_isb_nが14桁だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '97812345678900',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_登録時既に使われている_isb_nは登録できない(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        Book::factory()->create([
            'isbn' => '9781234567891',
        ]);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_登録時出版日が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('published_date');
    }

    public function test_登録時出版日が無効な日付だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-02-30',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('published_date');
    }

    public function test_登録時説明が空でも書籍が登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $book = Book::where('isbn', '9781234567891')->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => null,
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);
    }

    public function test_登録時画像_ur_lが空でも書籍が登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => '',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $book = Book::where('isbn', '9781234567891')->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => null,
        ]);
    }

    public function test_登録時画像_ur_lが無効な形式だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'wrong_image',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('image_url');
    }

    private function createUrl(int $length): string
    {
        $prefix = 'https://example.com/';

        return $prefix.str_repeat('a', $length - strlen($prefix));
    }

    public function test_登録時画像_ur_lが255文字だと書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => $this->createUrl(255),
            'genres' => $genres->pluck('id')->all(),
        ]);

        $book = Book::where('isbn', '9781234567891')->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => $this->createUrl(255),
        ]);
    }

    public function test_登録時画像_ur_lが256文字だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => $this->createUrl(256),
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('image_url');
    }

    public function test_登録時ジャンルが選択されていないとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => '',
        ]);

        $response->assertSessionHasErrors('genres');
    }

    public function test_登録時ジャンルが配列でないとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => 1,
        ]);

        $response->assertSessionHasErrors('genres');
    }

    public function test_登録時存在しないジャンルを選択するとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => [9999],
        ]);

        $response->assertSessionHasErrors('genres.*');
    }

    public function test_更新時タイトルが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_更新時タイトルが255文字だと書籍を更新できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => str_repeat('あ', 255),
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => str_repeat('あ', 255),
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);
    }

    public function test_更新時タイトルが256文字だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => str_repeat('あ', 256),
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_更新時著者が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト書籍',
            'author' => '',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('author');
    }

    public function test_更新時著者が255文字だと書籍を更新できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'テスト書籍',
            'author' => str_repeat('あ', 255),
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => str_repeat('あ', 255),
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);
    }

    public function test_更新時著者が256文字だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト書籍',
            'author' => str_repeat('あ', 256),
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('author');
    }

    public function test_更新時_isb_nが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_更新時_isb_nが12桁だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '978123456789',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_更新時_isb_nが14桁だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '97812345678900',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_更新時更新対象以外に既に使われている_isb_nには更新できない(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567890',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567890',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_更新時出版日が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('published_date');
    }

    public function test_更新時出版日が無効な日付だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-02-30',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('published_date');
    }

    public function test_更新時説明が空でも書籍が更新できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => null,
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);
    }

    public function test_更新時画像_ur_lが空でも書籍が更新できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => '',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => null,
        ]);
    }

    public function test_更新時画像_ur_lが無効な形式だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'wrong_url',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('image_url');
    }

    public function test_更新時画像_ur_lが255文字だと書籍を更新できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => $this->createUrl(255),
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => $this->createUrl(255),
        ]);
    }

    public function test_更新時画像_ur_lが256文字だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => $this->createUrl(256),
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertSessionHasErrors('image_url');
    }

    public function test_更新時ジャンルが選択されていないとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => '',
        ]);

        $response->assertSessionHasErrors('genres');
    }

    public function test_更新時ジャンルが配列でないとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => 1,
        ]);

        $response->assertSessionHasErrors('genres');
    }

    public function test_更新時存在しないジャンルを選択するとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => [9999],
        ]);

        $response->assertSessionHasErrors('genres.*');
    }
}
