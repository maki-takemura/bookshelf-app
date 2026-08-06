<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーは自身の通知一覧を表示できる(): void
    {
        $user = User::factory()->create();
        $user->notify(
            new ReadingPlanReminderNotification(
                readingPlanId: 1,
                timing: 'three_days_before',
                title: '自身の通知',
                body: '自身の通知本文',
            )
        );

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');
        $response->assertViewHas('notifications', function ($notifications): bool {
            return $notifications->count() === 1
                && $notifications->first()->data['title'] === '自身の通知';
        });
    }

    public function test_認証済みユーザーの通知一覧には他のユーザーの通知が表示されない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->notify(
            new ReadingPlanReminderNotification(
                readingPlanId: 1,
                timing: 'three_days_before',
                title: '他のユーザーの通知',
                body: '他のユーザーの通知本文',
            )
        );

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewHas('notifications', function ($notifications): bool {
            return $notifications->isEmpty();
        });
    }

    public function test_未認証ユーザーは通知一覧を表示できない(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_認証済みユーザーは自身の未読通知を既読にできる(): void
    {
        $user = User::factory()->create();
        $user->notify(
            new ReadingPlanReminderNotification(
                readingPlanId: 1,
                timing: 'three_days_before',
                title: '自身の通知',
                body: '自身の通知本文',
            )
        );
        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('notifications.index'));
        $response->assertSessionHas('success', '通知を既読にしました。');
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_認証済みユーザーは他のユーザーの通知を既読にできない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherUser->notify(
            new ReadingPlanReminderNotification(
                readingPlanId: 1,
                timing: 'three_days_before',
                title: '他のユーザーの通知',
                body: '他のユーザーの通知本文',
            )
        );
        $notification = $otherUser->notifications()->first();

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id));

        $response->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_未認証ユーザーは通知を既読にできない(): void
    {
        $user = User::factory()->create();
        $user->notify(
            new ReadingPlanReminderNotification(
                readingPlanId: 1,
                timing: 'three_days_before',
                title: '通知タイトル',
                body: '通知本文',
            )
        );
        $notification = $user->notifications()->first();

        $response = $this->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('login'));
        $this->assertNull($notification->fresh()->read_at);
    }
}
