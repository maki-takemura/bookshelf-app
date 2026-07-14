<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_登録時ジャンル名が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_登録時ジャンル名は255文字まで入力できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => str_repeat('あ', 255),
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'name' => str_repeat('あ', 255),
        ]);
    }

    public function test_登録時ジャンル名が256文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => str_repeat('あ', 256),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_登録時既に使われているジャンル名は登録できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'テストジャンル',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_更新時ジャンル名が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_更新時ジャンル名は255文字まで入力できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => str_repeat('あ', 255),
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => str_repeat('あ', 255),
        ]);
    }

    public function test_更新時ジャンル名が256文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => str_repeat('あ', 256),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_更新時自身のジャンル名のまま更新できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => 'テストジャンル',
        ]);

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => 'テストジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'テストジャンル',
        ]);
    }

    public function test_更新時更新対象以外で既に使われているジャンル名には更新できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '更新前ジャンル',
        ]);
        Genre::factory()->create([
            'name' => '更新テストジャンル',
        ]);

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => '更新テストジャンル',
        ]);

        $response->assertSessionHasErrors('name');
    }
}
