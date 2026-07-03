<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'user' => '山田太郎',
                'books' => [
                    '吾輩は猫である',
                    '人を動かす',
                    'リーダブルコード',
                ],
            ],
            [
                'user' => '鈴木花子',
                'books' => [
                    '7つの習慣',
                    '坊っちゃん',
                    'サピエンス全史',
                    'Clean Code',
                ],
            ],
            [
                'user' => '田中一郎',
                'books' => [
                    '嫌われる勇気',
                    '火花',
                    'FACTFULNESS',
                    'コンテナ物語',
                    '吾輩は猫である',
                ],
            ],
            [
                'user' => '佐藤美咲',
                'books' => [
                    '人を動かす',
                    'リーダブルコード',
                    '7つの習慣',
                ],
            ],
            [
                'user' => '高橋健太',
                'books' => [
                    '坊っちゃん',
                    'サピエンス全史',
                    'Clean Code',
                    '嫌われる勇気',
                ],
            ],
        ];

        foreach ($items as $item) {
            $user = User::firstWhere('name', $item['user']);
            $bookNames = $item['books'];
            $bookIds = Book::whereIn('title', $bookNames)->pluck('id')->toArray();

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}
