<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーはジャンル一覧画面が表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);
    }

    public function test_認証済みユーザーはジャンル詳細画面が表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertViewHas('genre');
    }

    public function test_認証済みユーザーはジャンル登録画面が表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.create'));

        $response->assertStatus(200);
    }

    public function test_認証済みユーザーはジャンル編集画面が表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.edit', $genre));

        $response->assertStatus(200);
        $response->assertViewHas('genre');
    }

    public function test_未認証ユーザーはジャンル一覧画面にアクセスできない(): void
    {
        $response = $this->get(route('genres.index'));

        $response->assertRedirect('/login');
    }

    public function test_未認証ユーザーはジャンル詳細画面にアクセスできない(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.show', $genre));

        $response->assertRedirect('/login');
    }

    public function test_未認証ユーザーはジャンル登録画面にアクセスできない(): void
    {
        $response = $this->get(route('genres.create'));

        $response->assertRedirect('/login');
    }

    public function test_未認証ユーザーはジャンル編集画面にアクセスできない(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.edit', $genre));

        $response->assertRedirect('/login');
    }

    public function test_認証済みユーザーはジャンルの登録ができる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'テストジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'name' => 'テストジャンル',
        ]);
    }

    public function test_認証済みユーザーはジャンルの更新ができる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => '更新後のジャンル名',
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '更新後のジャンル名',
        ]);
    }

    public function test_認証済みユーザーは紐づく書籍のないジャンルの削除ができる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_認証済みユーザーであっても紐づく書籍のあるジャンルは削除できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    public function test_未認証ユーザーはジャンル登録ができない(): void
    {
        $response = $this->post(route('genres.store'), [
            'name' => 'テストジャンル',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('genres', [
            'name' => 'テストジャンル',
        ]);
    }

    public function test_未認証ユーザーはジャンルの更新ができない(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->put(route('genres.update', $genre), [
            'name' => '更新後のジャンル名',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('genres', [
            'name' => 'テストジャンル',
        ]);
    }

    public function test_未認証ユーザーは書籍の紐づかないジャンルであっても削除できない(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->delete(route('genres.destroy', $genre));

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }
}
