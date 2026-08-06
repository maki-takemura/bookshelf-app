<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ConsoleKernelTest extends TestCase
{
    public function test_通知処理コマンドが毎日20時に実行されるようスケジュール登録されている(): void
    {
        $schedule = app(Schedule::class);

        $event = collect($schedule->events())
            ->first(fn ($event) => str_contains(
                $event->command,
                'app:process-reading-plan-reminders'
            ));

        $this->assertNotNull($event);
        $this->assertSame('0 20 * * *', $event->expression);
    }
}
