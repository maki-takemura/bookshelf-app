<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    /**
     * 通知内容を受け取る
     */
    public function __construct(
        private readonly int $readingPlanId,
        private readonly string $timing,
        private readonly string $title,
        private readonly string $body,
    ) {}

    /**
     * 通知の配信方法を指定する
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * データベースへ保存する通知内容を返す
     *
     * @return array<string, int|string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reading_plan_id' => $this->readingPlanId,
            'timing' => $this->timing,
            'title' => $this->title,
            'body' => $this->body,
        ];
    }
}
