<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーはレビューにいいねを追加できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('review_likes', [
            'review_id' => $review->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_認証済みユーザーは自身が追加したレビューのいいねを解除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);
        $review->likedByUsers()->attach($user->id);

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_未認証ユーザーはレビューにいいねできない(): void
    {
        $review = Review::factory()->create();

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect(route('login'));
    }
}
