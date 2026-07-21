<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_登録時評価が未選択だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => '',
            'comment' => 'テストコメントです',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_登録時評価が1だとレビューが登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 1,
            'comment' => 'テストコメントです',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => 'テストコメントです',
        ]);
    }

    public function test_登録時評価が0だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 0,
            'comment' => 'テストコメントです',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_登録時評価が5だとレビューが登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 5,
            'comment' => 'テストコメントです',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストコメントです',
        ]);
    }

    public function test_登録時評価が6だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 6,
            'comment' => 'テストコメントです',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_登録時コメントが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 3,
            'comment' => '',
        ]);

        $response->assertSessionHasErrors('comment');
    }

    public function test_登録時コメントが1000文字だとレビューが登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 3,
            'comment' => str_repeat('あ', 1000),
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => str_repeat('あ', 1000),
        ]);
    }

    public function test_登録時コメントが1001文字だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 3,
            'comment' => str_repeat('あ', 1001),
        ]);

        $response->assertSessionHasErrors('comment');
    }

    public function test_更新時評価が未選択だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストです',
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => '',
            'comment' => '更新テストです',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_更新時評価が1だとレビューが更新できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストです',
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 1,
            'comment' => '更新テストです',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 1,
            'comment' => '更新テストです',
        ]);
    }

    public function test_更新時評価が0だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストです',
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 0,
            'comment' => '更新テストです',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_更新時評価が5だとレビューが更新できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストです',
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 5,
            'comment' => '更新テストです',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '更新テストです',
        ]);
    }

    public function test_更新時評価が6だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストです',
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 6,
            'comment' => '更新テストです',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_更新時コメントが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストです',
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 3,
            'comment' => '',
        ]);

        $response->assertSessionHasErrors('comment');
    }

    public function test_更新時コメントが1000文字だとレビューが更新できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストです',
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 3,
            'comment' => str_repeat('あ', 1000),
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => str_repeat('あ', 1000),
        ]);
    }

    public function test_更新時コメントが1001文字だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストです',
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 3,
            'comment' => str_repeat('あ', 1001),
        ]);

        $response->assertSessionHasErrors('comment');
    }
}
