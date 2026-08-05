<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookIsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_検索した_isb_nに対応する書籍情報を取得できる(): void
    {
        $user = User::factory()->create();
        $isbn = '9784297132347';

        Http::fake(function ($request) use ($isbn) {
            if (
                str_starts_with(
                    $request->url(),
                    'https://www.googleapis.com/books/v1/volumes'
                )
                && $request['q'] === 'isbn:'.$isbn
            ) {
                return Http::response([
                    'items' => [
                        [
                            'volumeInfo' => [
                                'title' => 'Laravel実践入門',
                                'authors' => ['山田太郎'],
                                'description' => 'Laravelの解説書です。',
                                'publishedDate' => '2024-01-01',
                                'imageLinks' => [
                                    'thumbnail' => 'https://example.com/image.jpg',
                                ],
                            ],
                        ],
                    ],
                ], 200);
            }

            return Http::response([
                'items' => [],
            ], 200);
        });

        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        Http::assertSent(function ($request) use ($isbn) {
            return str_starts_with(
                $request->url(),
                'https://www.googleapis.com/books/v1/volumes'
            )
                && $request['q'] === 'isbn:'.$isbn;
        });
        $response->assertOk();
        $response->assertJson([
            'title' => 'Laravel実践入門',
            'author' => '山田太郎',
            'description' => 'Laravelの解説書です。',
            'published_date' => '2024-01-01',
            'image_url' => 'https://example.com/image.jpg',
        ]);
    }

    public function test_検索した_isb_nに対応する書籍が存在しない場合は404を返す(): void
    {
        $user = User::factory()->create();
        $isbn = '9784297132347';

        Http::fake(function ($request) use ($isbn) {
            if (
                str_starts_with(
                    $request->url(),
                    'https://www.googleapis.com/books/v1/volumes'
                )
                && $request['q'] === 'isbn:'.$isbn
            ) {
                return Http::response([
                    'items' => [],
                ], 200);
            }

            return Http::response([
                'items' => [],
            ], 200);
        });

        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        Http::assertSent(function ($request) use ($isbn) {
            return str_starts_with(
                $request->url(),
                'https://www.googleapis.com/books/v1/volumes'
            )
                && $request['q'] === 'isbn:'.$isbn;
        });
        $response->assertNotFound();
        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }

    public function test_外部_ap_iとの通信に失敗した場合は500を返す(): void
    {
        $user = User::factory()->create();
        $isbn = '9784297132347';

        Http::fake(function ($request) use ($isbn) {
            if (
                str_starts_with(
                    $request->url(),
                    'https://www.googleapis.com/books/v1/volumes'
                )
                && $request['q'] === 'isbn:'.$isbn
            ) {
                return Http::failedConnection();
            }

            return Http::response([], 200);
        });

        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'API通信エラーが発生しました。',
        ]);
    }

    public function test_外部_ap_iがエラーレスポンスを返した場合は500を返す(): void
    {
        $user = User::factory()->create();
        $isbn = '9784297132347';

        Http::fake(function ($request) use ($isbn) {
            if (
                str_starts_with(
                    $request->url(),
                    'https://www.googleapis.com/books/v1/volumes'
                )
                && $request['q'] === 'isbn:'.$isbn
            ) {
                return Http::response([], 500);
            }

            return Http::response([], 200);
        });

        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        Http::assertSent(function ($request) use ($isbn) {
            return str_starts_with(
                $request->url(),
                'https://www.googleapis.com/books/v1/volumes'
            )
                && $request['q'] === 'isbn:'.$isbn;
        });
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'API通信エラーが発生しました。',
        ]);
    }

    public function test_12桁の_isb_nはバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $isbn = '978429713234';

        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        $response->assertBadRequest();
        $response->assertJson([
            'error' => 'ISBNは13桁で入力してください。',
        ]);
    }

    public function test_14桁の_isb_nはバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $isbn = '97842971323478';

        $response = $this->actingAs($user)->get("/books/isbn/{$isbn}");

        $response->assertBadRequest();
        $response->assertJson([
            'error' => 'ISBNは13桁で入力してください。',
        ]);
    }
}
