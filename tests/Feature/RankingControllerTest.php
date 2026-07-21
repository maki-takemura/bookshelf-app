<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_すべてのユーザーにランキング画面が表示される(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
    }

    public function test_ランキング画面には平均評価上位10件の書籍が表示される(): void
    {
        $topBooks = Book::factory()->count(10)->create();
        foreach ($topBooks as $topBook) {
            Review::factory()->create([
                'book_id' => $topBook->id,
                'rating' => 5,
            ]);
        }

        $lowBook = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $lowBook->id,
            'rating' => 1,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($topBooks, $lowBook) {
            $this->assertCount(10, $rankedBooks);

            foreach ($topBooks as $topBook) {
                $this->assertTrue(
                    $rankedBooks->contains('id', $topBook->id)
                );
            }

            $this->assertFalse(
                $rankedBooks->contains('id', $lowBook->id)
            );

            return true;
        });
    }

    public function test_ランキング画面の書籍は平均評価の降順に表示される(): void
    {
        $ratingPatterns = [
            [5, 5], // 5.0
            [5, 4], // 4.5
            [4, 4], // 4.0
            [4, 3], // 3.5
        ];

        foreach ($ratingPatterns as $ratings) {
            $book = Book::factory()->create();

            foreach ($ratings as $rating) {
                Review::factory()->create([
                    'book_id' => $book->id,
                    'rating' => $rating,
                ]);
            }
        }

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $rankedBooks = $response->viewData('rankedBooks');
        $this->assertEquals(
            [5.0, 4.5, 4.0, 3.5],
            $rankedBooks->pluck('reviews_avg_rating')->all()
        );
    }

    public function test_ランキング画面にはレビューなしの書籍は表示されない(): void
    {
        $reviewedBooks = Book::factory()->count(10)->create();

        foreach ($reviewedBooks as $reviewedBook) {
            Review::factory()->create([
                'book_id' => $reviewedBook->id,
            ]);
        }

        $notReviewedBook = Book::factory()->create();

        $response = $this->get(route('ranking.index'));

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($reviewedBooks, $notReviewedBook) {
            foreach ($reviewedBooks as $reviewedBook) {
                $this->assertTrue(
                    $rankedBooks->contains('id', $reviewedBook->id)
                );
            }

            $this->assertFalse(
                $rankedBooks->contains('id', $notReviewedBook->id)
            );

            return true;
        });
    }
}
