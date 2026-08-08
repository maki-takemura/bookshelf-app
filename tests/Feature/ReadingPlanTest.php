<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーは自身の読書計画一覧を表示できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertViewIs('reading-plans.index');
        $response->assertSee($book->title);
    }

    public function test_認証済みユーザーは自身の読書計画一覧で状態を指定して絞り込み表示ができる(): void
    {
        $user = User::factory()->create();
        $inProgressBook = Book::factory()->create([
            'title' => '進行中の読書計画用書籍',
        ]);
        $completedBook = Book::factory()->create([
            'title' => '読了済みの読書計画用書籍',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $inProgressBook->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $completedBook->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index', [
            'status' => ReadingPlanStatus::Completed->value,
        ]));

        $response->assertOk();
        $response->assertSee($completedBook->title);
        $response->assertDontSee($inProgressBook->title);
    }

    public function test_認証済みユーザーの読書計画一覧には他のユーザーの読書計画が表示されない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $userBook = Book::factory()->create([
            'title' => '自身の読書計画の書籍',
        ]);
        $otherUserBook = Book::factory()->create([
            'title' => '他ユーザーの読書計画の書籍',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $userBook->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $otherUserBook->id,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertSee($userBook->title);
        $response->assertDontSee($otherUserBook->title);
    }

    public function test_認証済みユーザーは読書計画作成画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reading-plans.create'));

        $response->assertOk();
        $response->assertViewIs('reading-plans.create');
    }

    public function test_認証済みユーザーは自身の読書計画編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));

        $response->assertOk();
        $response->assertViewIs('reading-plans.edit');
    }

    public function test_認証済みユーザーでも読了済みの読書計画編集画面は表示できない(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));

        $response->assertForbidden();
    }

    public function test_認証済みユーザーでも他のユーザーの読書計画編集画面は表示できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));

        $response->assertForbidden();
    }

    public function test_未認証ユーザーは読書計画一覧画面を表示できない(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_未認証ユーザーは読書計画作成画面を表示できない(): void
    {
        $response = $this->get(route('reading-plans.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_未認証ユーザーは読書計画編集画面を表示できない(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->get(route('reading-plans.edit', $readingPlan));

        $response->assertRedirect(route('login'));
    }

    public function test_認証済みユーザーは読書計画を登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => now()->addWeek()->toDateString(),
            ]);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を作成しました。');
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_認証済みユーザーは自身の読書計画を更新できる(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->addWeeks(2)->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を更新しました。');
        $readingPlan->refresh();
        $this->assertTrue($readingPlan->target_date->isSameDay(now()->addWeeks(2)));
    }

    public function test_期限切れの読書計画を更新するとステータスが進行中に変更される(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Expired,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_認証済みユーザーでも読了済みの読書計画は更新できない(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);
        $originalTargetDate = $readingPlan->target_date;

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->addWeeks(2)->toDateString(),
        ]);

        $response->assertForbidden();
        $readingPlan->refresh();
        $this->assertTrue($readingPlan->target_date->isSameDay($originalTargetDate));
    }

    public function test_認証済みユーザーでも他のユーザーの読書計画は更新できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        $originalTargetDate = $readingPlan->target_date;

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->addWeeks(2)->toDateString(),
        ]);

        $response->assertForbidden();
        $readingPlan->refresh();
        $this->assertTrue($readingPlan->target_date->isSameDay($originalTargetDate));
    }

    public function test_認証済みユーザーは自身の読書計画を削除できる(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を削除しました。');
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_認証済みユーザーでも他のユーザーの読書計画は削除できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertForbidden();
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_認証済みユーザーは自身の読書計画を完了できる(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を完了しました。');
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);
    }

    public function test_認証済みユーザーでも他のユーザーの読書計画は完了できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan));

        $response->assertForbidden();
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_読了済みの読書計画に再度完了処理を実行しても更新されない(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
            'updated_at' => now()->subMinute(),
        ]);
        $originalUpdatedAt = $readingPlan->updated_at;

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan));

        $response->assertForbidden();
        $readingPlan->refresh();
        $this->assertEquals(
            $originalUpdatedAt,
            $readingPlan->updated_at
        );
    }

    public function test_未認証ユーザーは読書計画を作成できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('reading_plans', [
            'book_id' => $book->id,
        ]);
    }

    public function test_未認証ユーザーは読書計画を更新できない(): void
    {
        $readingPlan = ReadingPlan::factory()->create();
        $originalTargetDate = $readingPlan->target_date;

        $response = $this->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->addWeeks(2)->toDateString(),
        ]);

        $response->assertRedirect(route('login'));
        $readingPlan->refresh();
        $this->assertTrue($readingPlan->target_date->isSameDay($originalTargetDate));
    }

    public function test_未認証ユーザーは読書計画を削除できない(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    public function test_未認証ユーザーは読書計画を読了できない(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->post(route('reading-plans.complete', $readingPlan));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_書籍が未選択の場合は読書計画を登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => '',
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertSessionHasErrors('book_id');
    }

    public function test_同一ユーザーは同一書籍の読書計画を重複して登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertSessionHasErrors('book_id');
        $this->assertDatabaseCount('reading_plans', 1);
    }

    public function test_存在しない書籍_i_dでは読書計画を登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => 9999,
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertSessionHasErrors('book_id');
    }

    public function test_期日が未入力の場合は読書計画を登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => '',
        ]);

        $response->assertSessionHasErrors('target_date');
    }

    public function test_期日が日付形式ではない場合は読書計画を登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => '日付ではない文字列',
        ]);

        $response->assertSessionHasErrors('target_date');
    }

    public function test_期日が過去の日付の場合は読書計画を登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('target_date');
    }

    public function test_期日が未入力の場合は読書計画を更新できない(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => '',
        ]);

        $response->assertSessionHasErrors('target_date');
    }

    public function test_期日が日付形式ではない場合は読書計画を更新できない(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => '日付ではない文字列',
        ]);

        $response->assertSessionHasErrors('target_date');
    }

    public function test_期日が過去の日付の場合は読書計画を更新できない(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->subDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('target_date');
    }
}
