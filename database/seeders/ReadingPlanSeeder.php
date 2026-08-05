<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yamada = User::where('name', '山田太郎')->firstOrFail();
        $suzuki = User::where('name', '鈴木花子')->firstOrFail();

        $books = Book::all()->keyBy('title');

        $items = [
            // 通知対象（進行中）
            [
                'user_id' => $yamada->id,
                'book_id' => $books['吾輩は猫である']->id,
                'target_date' => Carbon::today()->addDays(3),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books['人を動かす']->id,
                'target_date' => Carbon::today(),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books['リーダブルコード']->id,
                'target_date' => Carbon::today()->subDays(3),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],

            // 通知対象外（進行中）
            [
                'user_id' => $yamada->id,
                'book_id' => $books['7つの習慣']->id,
                'target_date' => Carbon::today()->addDays(5),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books['坊っちゃん']->id,
                'target_date' => Carbon::today()->subDay(),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],

            // 完了
            [
                'user_id' => $yamada->id,
                'book_id' => $books['サピエンス全史']->id,
                'target_date' => Carbon::today()->addDays(3),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => Carbon::today(),
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books['Clean Code']->id,
                'target_date' => Carbon::today(),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => Carbon::today(),
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books['嫌われる勇気']->id,
                'target_date' => Carbon::today()->subDays(3),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => Carbon::today()->subDays(2),
            ],

            // 期限切れ
            [
                'user_id' => $yamada->id,
                'book_id' => $books['火花']->id,
                'target_date' => Carbon::today()->subDays(5),
                'status' => ReadingPlanStatus::Expired,
                'completed_at' => null,
            ],

            // 認可確認用（別ユーザー）
            [
                'user_id' => $suzuki->id,
                'book_id' => $books['FACTFULNESS']->id,
                'target_date' => Carbon::today()->addDays(3),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
        ];

        foreach ($items as $item) {
            ReadingPlan::create($item);
        }
    }
}
