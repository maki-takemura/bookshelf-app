<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーはレビューを投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 3,
            'comment' => 'テストコメントです',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストコメントです',
        ]);
    }

    public function test_認証済みかつ投稿者本人はレビュー編集画面が表示される(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('reviews.edit', $review));

        $response->assertStatus(200);
        $response->assertViewHas('review');
    }

    public function test_認証済みだが投稿者本人以外のユーザーはレビュー編集画面へアクセスできない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    public function test_未認証ユーザーはレビュー編集画面にアクセスできない(): void
    {
        $review = Review::factory()->create();

        $response = $this->get(route('reviews.edit', $review));

        $response->assertRedirect(route('login'));
    }

    public function test_認証済みかつ投稿者本人はレビューを更新できる(): void
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
            'comment' => '更新テストです',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新テストです',
        ]);
    }

    public function test_認証済みかつ投稿者本人はレビューを削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_認証済みだが投稿者本人以外のユーザーはレビューを更新できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストコメントです',
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 3,
            'comment' => '不正な更新です',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', [
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'テストコメントです',
        ]);
    }

    public function test_認証済みだが投稿者本人以外のユーザーはレビューを削除できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('reviews.destroy', $review));

        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_未認証ユーザーはレビューを投稿できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('reviews.store', $book));

        $response->assertRedirect(route('login'));
    }

    public function test_未認証ユーザーはレビューを更新できない(): void
    {
        $review = Review::factory()->create();

        $response = $this->put(route('reviews.update', $review));

        $response->assertRedirect(route('login'));
    }

    public function test_未認証ユーザーはレビューを削除できない(): void
    {
        $review = Review::factory()->create();

        $response = $this->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('login'));
    }
}
