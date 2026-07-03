<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'user' => '山田太郎',
                'book' => '吾輩は猫である',
                'rating' => 5,
                'comment' => 'とても読みやすく、一気に最後まで読めました。',
            ],
            [
                'user' => '鈴木花子',
                'book' => '吾輩は猫である',
                'rating' => 4,
                'comment' => '実践で役立つ内容が多く参考になりました。',
            ],
            [
                'user' => '田中一郎',
                'book' => '吾輩は猫である',
                'rating' => 3,
                'comment' => '内容は良かったですが少し難しく感じました。',
            ],
            [
                'user' => '佐藤美咲',
                'book' => '人を動かす',
                'rating' => 5,
                'comment' => '説明が丁寧で、最後まで興味深く読めました。',
            ],
            [
                'user' => '高橋健太',
                'book' => '人を動かす',
                'rating' => 4,
                'comment' => '具体例が多く、理解を深めるのに役立ちました。',
            ],
            [
                'user' => '山田太郎',
                'book' => 'リーダブルコード',
                'rating' => 5,
                'comment' => 'とても読みやすく、一気に最後まで読めました。',
            ],
            [
                'user' => '鈴木花子',
                'book' => 'リーダブルコード',
                'rating' => 4,
                'comment' => '実践で役立つ内容が多く参考になりました。',
            ],
            [
                'user' => '田中一郎',
                'book' => 'リーダブルコード',
                'rating' => 3,
                'comment' => '内容は良かったですが少し難しく感じました。',
            ],
            [
                'user' => '佐藤美咲',
                'book' => '7つの習慣',
                'rating' => 5,
                'comment' => '説明が丁寧で、最後まで興味深く読めました。',
            ],
            [
                'user' => '高橋健太',
                'book' => '7つの習慣',
                'rating' => 4,
                'comment' => '具体例が多く、理解を深めるのに役立ちました。',
            ],
            [
                'user' => '山田太郎',
                'book' => '坊っちゃん',
                'rating' => 5,
                'comment' => 'とても読みやすく、一気に最後まで読めました。',
            ],
            [
                'user' => '鈴木花子',
                'book' => '坊っちゃん',
                'rating' => 4,
                'comment' => '実践で役立つ内容が多く参考になりました。',
            ],
            [
                'user' => '田中一郎',
                'book' => '坊っちゃん',
                'rating' => 3,
                'comment' => '内容は良かったですが少し難しく感じました。',
            ],
            [
                'user' => '佐藤美咲',
                'book' => 'サピエンス全史',
                'rating' => 5,
                'comment' => '説明が丁寧で、最後まで興味深く読めました。',
            ],
            [
                'user' => '高橋健太',
                'book' => 'サピエンス全史',
                'rating' => 4,
                'comment' => '具体例が多く、理解を深めるのに役立ちました。',
            ],
            [
                'user' => '山田太郎',
                'book' => 'サピエンス全史',
                'rating' => 5,
                'comment' => 'とても読みやすく、一気に最後まで読めました。',
            ],
            [
                'user' => '鈴木花子',
                'book' => 'サピエンス全史',
                'rating' => 4,
                'comment' => '実践で役立つ内容が多く参考になりました。',
            ],
            [
                'user' => '田中一郎',
                'book' => 'Clean Code',
                'rating' => 3,
                'comment' => '内容は良かったですが少し難しく感じました。',
            ],
            [
                'user' => '佐藤美咲',
                'book' => 'Clean Code',
                'rating' => 5,
                'comment' => '説明が丁寧で、最後まで興味深く読めました。',
            ],
            [
                'user' => '高橋健太',
                'book' => 'Clean Code',
                'rating' => 4,
                'comment' => '具体例が多く、理解を深めるのに役立ちました。',
            ],
            [
                'user' => '山田太郎',
                'book' => '嫌われる勇気',
                'rating' => 5,
                'comment' => 'とても読みやすく、一気に最後まで読めました。',
            ],
            [
                'user' => '鈴木花子',
                'book' => '嫌われる勇気',
                'rating' => 4,
                'comment' => '実践で役立つ内容が多く参考になりました。',
            ],
            [
                'user' => '田中一郎',
                'book' => '嫌われる勇気',
                'rating' => 3,
                'comment' => '内容は良かったですが少し難しく感じました。',
            ],
            [
                'user' => '佐藤美咲',
                'book' => '火花',
                'rating' => 5,
                'comment' => '説明が丁寧で、最後まで興味深く読めました。',
            ],
            [
                'user' => '高橋健太',
                'book' => '火花',
                'rating' => 4,
                'comment' => '具体例が多く、理解を深めるのに役立ちました。',
            ],
            [
                'user' => '山田太郎',
                'book' => '火花',
                'rating' => 5,
                'comment' => 'とても読みやすく、一気に最後まで読めました。',
            ],
            [
                'user' => '鈴木花子',
                'book' => 'FACTFULNESS',
                'rating' => 4,
                'comment' => '実践で役立つ内容が多く参考になりました。',
            ],
            [
                'user' => '田中一郎',
                'book' => 'FACTFULNESS',
                'rating' => 3,
                'comment' => '内容は良かったですが少し難しく感じました。',
            ],
            [
                'user' => '佐藤美咲',
                'book' => 'FACTFULNESS',
                'rating' => 5,
                'comment' => '説明が丁寧で、最後まで興味深く読めました。',
            ],
            [
                'user' => '高橋健太',
                'book' => 'コンテナ物語',
                'rating' => 4,
                'comment' => '具体例が多く、理解を深めるのに役立ちました。',
            ],
            [
                'user' => '山田太郎',
                'book' => 'コンテナ物語',
                'rating' => 5,
                'comment' => 'とても読みやすく、一気に最後まで読めました。',
            ],
            [
                'user' => '鈴木花子',
                'book' => 'コンテナ物語',
                'rating' => 4,
                'comment' => '実践で役立つ内容が多く参考になりました。',
            ],
        ];

        foreach ($items as $item) {
            $userId = User::where('name', $item['user'])->value('id');
            $bookId = Book::where('title', $item['book'])->value('id');
            Review::create([
                'user_id' => $userId,
                'book_id' => $bookId,
                'rating' => $item['rating'],
                'comment' => $item['comment'],
            ]);
        }
    }
}
