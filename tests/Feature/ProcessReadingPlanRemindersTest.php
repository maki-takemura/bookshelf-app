<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessReadingPlanRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_期日を過ぎた進行中の読書計画はコマンド実行時に期限切れへ更新される(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => today()->subDay(),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Expired->value,
        ]);
    }

    public function test_期日当日の進行中の読書計画は期限切れへ更新されない(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => today(),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_完了済みの読書計画は期日を過ぎても期限切れへ更新されない(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Completed,
            'target_date' => today()->subDay(),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);
    }

    public function test_期日の3日前に進行中の読書計画の予告リマインド通知が作成される(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => today()->addDays(3),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $notification = $readingPlan->user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame($readingPlan->id, $notification->data['reading_plan_id']);
        $this->assertSame('three_days_before', $notification->data['timing']);
        $this->assertSame('読了予定日まであと3日です', $notification->data['title']);
        $this->assertSame('読書計画の進み具合を確認しましょう。', $notification->data['body']);
    }

    public function test_期日当日に進行中の読書計画の最終リマインド通知が作成される(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => today(),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $notification = $readingPlan->user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame($readingPlan->id, $notification->data['reading_plan_id']);
        $this->assertSame('on_due_date', $notification->data['timing']);
        $this->assertSame('今日は読了予定日です', $notification->data['title']);
        $this->assertSame('読書計画の達成状況を確認しましょう。', $notification->data['body']);
    }

    public function test_期日の3日後に期限切れの読書計画の再エンゲージメント通知が作成される(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Expired,
            'target_date' => today()->subDays(3),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $notification = $readingPlan->user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame($readingPlan->id, $notification->data['reading_plan_id']);
        $this->assertSame('three_days_after', $notification->data['timing']);
        $this->assertSame('読了予定日を3日過ぎています', $notification->data['title']);
        $this->assertSame('読書計画の達成状況を確認してください。', $notification->data['body']);
    }

    public function test_通知は対象の読書計画を所有するユーザーに作成される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => today()->addDays(3),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $this->assertTrue(
            $user->notifications()
                ->where('data->reading_plan_id', $readingPlan->id)
                ->exists()
        );
        $this->assertFalse(
            $otherUser->notifications()
                ->where('data->reading_plan_id', $readingPlan->id)
                ->exists()
        );
    }

    public function test_通知対象日以外の読書計画には通知が作成されない(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => today()->addDays(2),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $this->assertFalse(
            $readingPlan->user
                ->notifications()
                ->where('data->reading_plan_id', $readingPlan->id)
                ->exists()
        );
    }

    public function test_完了済みの読書計画には通知が作成されない(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Completed,
            'target_date' => today()->addDays(3),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $this->assertFalse(
            $readingPlan->user
                ->notifications()
                ->where('data->reading_plan_id', $readingPlan->id)
                ->exists()
        );
    }

    public function test_同じ読書計画の同じタイミングの通知はコマンドを再実行しても重複して作成されない(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => today()->addDays(3),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $notificationCount = $readingPlan->user
            ->notifications()
            ->where('data->reading_plan_id', $readingPlan->id)
            ->where('data->timing', 'three_days_before')
            ->count();
        $this->assertSame(1, $notificationCount);
    }

    public function test_同じ読書計画でも異なるタイミングの通知はそれぞれ作成される(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => today()->addDays(3),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $readingPlan->update([
            'target_date' => today(),
        ]);

        $this->artisan('app:process-reading-plan-reminders')
            ->assertSuccessful();

        $this->assertTrue(
            $readingPlan->user
                ->notifications()
                ->where('data->reading_plan_id', $readingPlan->id)
                ->where('data->timing', 'three_days_before')
                ->exists()
        );

        $this->assertTrue(
            $readingPlan->user
                ->notifications()
                ->where('data->reading_plan_id', $readingPlan->id)
                ->where('data->timing', 'on_due_date')
                ->exists()
        );
    }
}
