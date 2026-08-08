<?php

namespace Tests\Unit;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_reading_plan_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->for($user)->create();

        $this->assertTrue($readingPlan->user->is($user));
    }

    public function test_reading_plan_belongs_to_book(): void
    {
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->for($book)->create();

        $this->assertTrue($readingPlan->book->is($book));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->assertInstanceOf(
            ReadingPlanStatus::class,
            $readingPlan->status
        );
    }

    public function test_target_date_is_cast_to_date(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'target_date' => '2026-08-31',
        ]);

        $this->assertInstanceOf(Carbon::class, $readingPlan->target_date);
    }

    public function test_completed_at_is_cast_to_datetime(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'completed_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $readingPlan->completed_at);
    }
}
