<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;

class ProcessReadingPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-reading-plan-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の期限切れ更新とリマインダー通知を実行する';

    /**
     * 読書計画の期限切れ更新とリマインダー通知を実行する
     */
    public function handle(): int
    {
        $today = today();

        ReadingPlan::query()
            ->where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', '<', $today)
            ->update([
                'status' => ReadingPlanStatus::Expired,
            ]);

        $reminderSettings = collect([
            [
                'target_date' => $today->copy()->addDays(3),
                'status' => ReadingPlanStatus::InProgress,
                'timing' => 'three_days_before',
                'title' => '読了予定日まであと3日です',
                'body' => '読書計画の進み具合を確認しましょう。',
            ],
            [
                'target_date' => $today,
                'status' => ReadingPlanStatus::InProgress,
                'timing' => 'on_due_date',
                'title' => '今日は読了予定日です',
                'body' => '読書計画の達成状況を確認しましょう。',
            ],
            [
                'target_date' => $today->copy()->subDays(3),
                'status' => ReadingPlanStatus::Expired,
                'timing' => 'three_days_after',
                'title' => '読了予定日を3日過ぎています',
                'body' => '読書計画の達成状況を確認してください。',
            ],
        ]);

        $reminderSettings->each(function (array $setting): void {
            ReadingPlan::query()
                ->with('user')
                ->where('status', $setting['status'])
                ->whereDate('target_date', $setting['target_date'])
                ->get()
                ->each(function (ReadingPlan $plan) use ($setting): void {
                    $alreadyNotified = $plan->user
                        ->notifications()
                        ->where('type', ReadingPlanReminderNotification::class)
                        ->where('data->reading_plan_id', $plan->id)
                        ->where('data->timing', $setting['timing'])
                        ->exists();

                    if ($alreadyNotified) {
                        return;
                    }

                    $plan->user->notify(
                        new ReadingPlanReminderNotification(
                            readingPlanId: $plan->id,
                            timing: $setting['timing'],
                            title: $setting['title'],
                            body: $setting['body'],
                        )
                    );
                });
        });

        return self::SUCCESS;
    }
}
