<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_すべてのユーザーに書籍一覧画面が表示される(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertViewHas('books');
    }

    public function test_すべてのユーザーに書籍詳細画面が表示される(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertViewHas('book');
    }

    public function test_認証済みユーザーは書籍登録画面が表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('books.create'));

        $response->assertStatus(200);
    }

    public function test_認証済みかつ作成者本人は書籍編集画面が表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('books.edit', $book));

        $response->assertStatus(200);
        $response->assertViewHas('book');
    }

    public function test_認証済みだが作成者本人以外のユーザーは書籍編集画面にアクセスできない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    public function test_未認証ユーザーは書籍登録画面にアクセスできない(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_未認証ユーザーは書籍編集画面にアクセスできない(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.edit', $book));

        $response->assertRedirect(route('login'));
    }

    public function test_認証済みユーザーは書籍の登録ができる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
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
            'title' => 'テスト書籍',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genres[0]->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genres[1]->id,
        ]);
    }

    public function test_認証済みかつ作成者本人は書籍情報を更新できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト',
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
            'title' => '更新テスト',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genres[0]->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genres[1]->id,
        ]);
    }

    public function test_認証済みかつ作成者本人は書籍を削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_認証済みだが作成者本人以外のユーザーは書籍を更新できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'テスト',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新テスト',
            'author' => '鈴木一郎',
            'isbn' => '9781234567891',
            'published_date' => '2000-01-01',
            'description' => 'テストです',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=99',
            'genres' => $genres->pluck('id')->all(),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $otherUser->id,
            'title' => 'テスト',
        ]);
    }

    public function test_認証済みだが作成者本人以外のユーザーは書籍を削除できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('books.destroy', $book));

        $response->assertForbidden();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    public function test_未認証ユーザーは書籍を登録できない(): void
    {
        $response = $this->post(route('books.store'));

        $response->assertRedirect(route('login'));
    }

    public function test_未認証ユーザーは書籍を更新できない(): void
    {
        $book = Book::factory()->create();
        $response = $this->put(route('books.update', $book), [
            'title' => '不正な更新',
        ]);
        $response->assertRedirect(route('login'));
    }

    public function test_未認証ユーザーは書籍を削除できない(): void
    {
        $book = Book::factory()->create();
        $response = $this->delete(route('books.destroy', $book));
        $response->assertRedirect(route('login'));
    }
}
