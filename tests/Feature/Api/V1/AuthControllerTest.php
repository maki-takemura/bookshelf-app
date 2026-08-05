<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_正しいメールアドレスとパスワードでログインするとトークンが発行される(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'token',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_登録されていないメールアドレスではログインできない(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'not-found@example.com',
            'password' => 'password',
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_誤ったパスワードではログインできない(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_メールアドレスが未入力の場合はバリデーションエラーになる(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_パスワードが未入力の場合はバリデーションエラーになる(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('password');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_認証済みユーザーがログアウトすると、成功レスポンスが返り、使用したトークンが削除される(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token');

        $response = $this->withToken($token->plainTextToken)->postJson('/api/v1/logout');

        $response->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_トークンなしでログアウト_ap_iにアクセスすると401が返る(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertUnauthorized();
    }
}
