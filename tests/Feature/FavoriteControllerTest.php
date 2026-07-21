<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーのお気に入り一覧画面には自身が登録した書籍のみが表示される(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(11)->create();
        $bookIds = $books->pluck('id')->toArray();
        $user->favoriteBooks()->attach($bookIds);

        $otherUser = User::factory()->create();
        $otherBook = Book::factory()->create();
        $otherUser->favoriteBooks()->attach($otherBook->id);

        $response = $this->actingAs($user)->get(route('favorites.index', ['page' => 1]));

        $response->assertStatus(200);
        $response->assertViewHas('books', function ($paginator) use ($otherBook) {
            $this->assertEquals(10, $paginator->perPage());
            $this->assertEquals(11, $paginator->total());
            $this->assertFalse(
                $paginator->getCollection()->contains('id', $otherBook->id)
            );

            return true;
        });
    }

    public function test_未認証ユーザーはお気に入り一覧画面にアクセスできない(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_認証済みユーザーは書籍をお気に入りに追加できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_認証済みユーザーは自身が追加した書籍のお気に入りを解除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $book->favoritedByUsers()->attach($user->id);

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_未認証ユーザーは書籍をお気に入りに追加できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('login'));
    }
}
