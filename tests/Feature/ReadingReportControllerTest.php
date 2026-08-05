<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーはマイ読書レポート画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewIs('reports.index');
    }

    public function test_認証済みユーザー自身のレビューだけが集計される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'user_id' => $otherUser->id,
            'rating' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 1
                && $stats['summary']['books_read'] === 1
                && $stats['summary']['average_rating'] === 5;
        });
    }

    public function test_レビューがない場合でもマイ読書レポート画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 0
                && $stats['summary']['books_read'] === 0
                && $stats['summary']['average_rating'] === 0
                && $stats['rating_distribution']->all() === [0, 0, 0, 0, 0]
                && $stats['top_rated_books']->isEmpty()
                && $stats['genre_ratings']->isEmpty();
        });
    }

    public function test_未認証ユーザーはマイ読書レポート画面にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_総レビュー数と読了冊数と平均評価が正しく表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $otherBook = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $otherBook->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 3
                && $stats['summary']['books_read'] === 2
                && $stats['summary']['average_rating'] === 4;
        });
    }

    public function test_評価ごとのレビュー件数が星1から星5まで正しく表示される(): void
    {
        $user = User::factory()->create();

        collect(range(1, 5))->each(function ($rating) use ($user) {
            Review::factory()->count($rating)->create([
                'user_id' => $user->id,
                'rating' => $rating,
            ]);
        });

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return $stats['rating_distribution']->all() === [1, 2, 3, 4, 5];
        });
    }

    public function test_高評価書籍には評価4以上の書籍が表示される(): void
    {
        $user = User::factory()->create();

        $highRatedBook = Book::factory()->create();
        $lowRatedBook = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $highRatedBook->id,
            'rating' => 4,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) use ($highRatedBook, $lowRatedBook) {
            $bookIds = $stats['top_rated_books']->pluck('id');

            return $bookIds->contains($highRatedBook->id)
                && ! $bookIds->contains($lowRatedBook->id);
        });
    }

    public function test_高評価書籍には評価の高い順に表示される(): void
    {
        $user = User::factory()->create();

        $rating4Book = Book::factory()->create();
        $rating5Book = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $rating4Book->id,
            'rating' => 4,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $rating5Book->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) use ($rating4Book, $rating5Book) {
            $highRatedBookIds = $stats['top_rated_books']->pluck('id')->all();

            return $highRatedBookIds === [$rating5Book->id, $rating4Book->id];
        });
    }

    public function test_高評価書籍には最大5件表示される(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(6)->create();
        foreach ($books as $book) {
            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => 5,
            ]);
        }

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['top_rated_books']->count() === 5;
        });
    }

    public function test_高評価書籍から書籍詳細画面へ移動できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee(route('books.show', $book));
    }

    public function test_ジャンルごとのレビュー件数が正しく表示される(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        $book1->genres()->attach($genre1);
        $book2->genres()->attach($genre1);
        $book3->genres()->attach($genre2);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) use ($genre1, $genre2) {
            $genre1Stats = $stats['genre_ratings']->firstWhere('id', $genre1->id);
            $genre2Stats = $stats['genre_ratings']->firstWhere('id', $genre2->id);

            return $stats['genre_ratings']->count() === 2
                && $genre1Stats['count'] === 2
                && $genre2Stats['count'] === 1;
        });
    }

    public function test_ジャンルごとの平均評価が正しく表示される(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $book1->genres()->attach($genre);
        $book2->genres()->attach($genre);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) use ($genre) {
            $genreStats = $stats['genre_ratings']->firstWhere('id', $genre->id);

            return $genreStats['average_rating'] === 4;
        });
    }

    public function test_ジャンル別ランキングは平均評価の高い順に表示される(): void
    {
        $user = User::factory()->create();

        $lowRatedGenre = Genre::factory()->create();
        $highRatedGenre = Genre::factory()->create();

        $lowRatedBook = Book::factory()->create();
        $highRatedBook = Book::factory()->create();

        $lowRatedBook->genres()->attach($lowRatedGenre);
        $highRatedBook->genres()->attach($highRatedGenre);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) use ($lowRatedGenre, $highRatedGenre) {
            return $stats['genre_ratings']->pluck('id')->all() === [$highRatedGenre->id, $lowRatedGenre->id];
        });
    }

    public function test_ジャンル別ランキングは最大5件表示される(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(6)->create();

        foreach ($genres as $genre) {
            $book = Book::factory()->create();
            $book->genres()->attach($genre);

            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => 5,
            ]);
        }

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['genre_ratings']->count() === 5;
        });
    }

    public function test_ジャンル別ランキングからジャンル詳細画面へ移動できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee(route('genres.show', $genre));
    }
}
