<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_タイトルに部分一致する書籍のみ表示される(): void
    {
        $matchingBook = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        $notMatchingBook = Book::factory()->create([
            'title' => 'PHP実践',
            'author' => '鈴木一郎',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
        ]));

        $response->assertOk();
        $response->assertSee($matchingBook->title);
        $response->assertDontSee($notMatchingBook->title);
    }

    public function test_著者名に部分一致する書籍のみ表示される(): void
    {
        $matchingBook = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        $notMatchingBook = Book::factory()->create([
            'title' => 'PHP実践',
            'author' => '鈴木一郎',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => '山田',
        ]));

        $response->assertOk();
        $response->assertSee($matchingBook->author);
        $response->assertDontSee($notMatchingBook->author);
    }

    public function test_検索条件に一致する書籍が存在しない場合は検索結果が表示されない(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => 'Python',
        ]));

        $response->assertOk();
        $response->assertViewHas('books', function ($books) {
            return $books->total() === 0;
        });
        $response->assertSee('書籍が見つかりませんでした。');
    }

    public function test_指定したジャンルに紐づく書籍のみ表示される(): void
    {
        $targetGenre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        $matchingBook = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);
        $matchingBook->genres()->attach($targetGenre->id);

        $notMatchingBook = Book::factory()->create([
            'title' => 'PHP実践',
        ]);
        $notMatchingBook->genres()->attach($otherGenre->id);

        $response = $this->get(route('books.index', [
            'genre' => $targetGenre->id,
        ]));

        $response->assertOk();
        $response->assertSee($matchingBook->title);
        $response->assertDontSee($notMatchingBook->title);
    }

    public function test_キーワードとジャンルを同時に指定すると両方の条件で絞り込まれる(): void
    {
        $targetGenre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        // キーワード・ジャンルともに一致
        $matchingBook = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);
        $matchingBook->genres()->attach($targetGenre->id);

        // キーワードのみ一致
        $keywordOnlyBook = Book::factory()->create([
            'title' => 'Laravel実践',
        ]);
        $keywordOnlyBook->genres()->attach($otherGenre->id);

        // ジャンルのみ一致
        $genreOnlyBook = Book::factory()->create([
            'title' => 'PHP入門',
        ]);
        $genreOnlyBook->genres()->attach($targetGenre->id);

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
            'genre' => $targetGenre->id,
        ]));

        $response->assertOk();
        $response->assertSee($matchingBook->title);
        $response->assertDontSee($keywordOnlyBook->title);
        $response->assertDontSee($genreOnlyBook->title);
    }

    public function test_デフォルトで登録日の新しい順に表示される(): void
    {
        $oldBook = Book::factory()->create([
            'title' => '古い書籍',
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'title' => '新しい書籍',
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index'));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($newBook, $oldBook) {
            return $books->pluck('id')->values()->all() === [
                $newBook->id,
                $oldBook->id,
            ];
        });
    }

    public function test_「古い順」を選択すると登録日の古い順に表示される(): void
    {
        $oldBook = Book::factory()->create([
            'title' => '古い書籍',
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'title' => '新しい書籍',
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'oldest',
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($oldBook, $newBook) {
            return $books->pluck('id')->values()->all() === [
                $oldBook->id,
                $newBook->id,
            ];
        });
    }

    public function test_「タイトル順」を選択するとタイトル昇順に表示される(): void
    {
        $bookC = Book::factory()->create([
            'title' => 'C言語入門',
        ]);

        $bookA = Book::factory()->create([
            'title' => 'A言語入門',
        ]);

        $bookB = Book::factory()->create([
            'title' => 'B言語入門',
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'title',
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($bookA, $bookB, $bookC) {
            return $books->pluck('id')->values()->all() === [
                $bookA->id,
                $bookB->id,
                $bookC->id,
            ];
        });
    }

    public function test_「評価順」を選択すると平均評価の高い順に表示され、レビューのない書籍は最後に表示される(): void
    {
        $highRatedBook = Book::factory()->create([
            'title' => '高評価書籍',
        ]);

        Review::factory()->count(2)->create([
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        $lowRatedBook = Book::factory()->create([
            'title' => '低評価書籍',
        ]);

        Review::factory()->count(2)->create([
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
        ]);

        $noReviewBook = Book::factory()->create([
            'title' => 'レビューなし書籍',
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'rating',
        ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($highRatedBook, $lowRatedBook, $noReviewBook) {
            return $books->pluck('id')->values()->all() === [
                $highRatedBook->id,
                $lowRatedBook->id,
                $noReviewBook->id,
            ];
        });
    }

    public function test_検索キーワードが255文字以内なら検索できる(): void
    {
        $keyword = str_repeat('あ', 255);

        $book = Book::factory()->create([
            'title' => $keyword,
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => $keyword,
        ]));

        $response->assertOk();
        $response->assertSee($book->title);
    }

    public function test_検索キーワードが256文字以上ならバリデーションエラーになる(): void
    {
        $response = $this->from(route('books.index'))
            ->get(route('books.index', [
                'keyword' => str_repeat('あ', 256),
            ]));

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('error', '検索キーワードは255文字以内で入力してください。');
    }

    public function test_存在しないジャンル_i_dならバリデーションエラーになる(): void
    {
        $response = $this->from(route('books.index'))
            ->get(route('books.index', [
                'genre' => 99999,
            ]));

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('error', '選択されたジャンルが存在しません。');
    }
}
