<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'user' => '山田太郎',
                'likedReviews' => [
                    ['user' => '鈴木花子', 'book' => '吾輩は猫である'],
                    ['user' => '田中一郎', 'book' => '吾輩は猫である'],
                    ['user' => '佐藤美咲', 'book' => '人を動かす'],
                    ['user' => '田中一郎', 'book' => 'リーダブルコード'],
                    ['user' => '鈴木花子', 'book' => '坊っちゃん'],
                    ['user' => '高橋健太', 'book' => 'サピエンス全史'],
                    ['user' => '佐藤美咲', 'book' => 'Clean Code'],
                    ['user' => '田中一郎', 'book' => '嫌われる勇気'],
                    ['user' => '佐藤美咲', 'book' => '火花'],
                    ['user' => '田中一郎', 'book' => 'FACTFULNESS'],
                ],
            ],
            [
                'user' => '鈴木花子',
                'likedReviews' => [
                    ['user' => '田中一郎', 'book' => '吾輩は猫である'],
                    ['user' => '佐藤美咲', 'book' => '人を動かす'],
                    ['user' => '高橋健太', 'book' => '7つの習慣'],
                    ['user' => '高橋健太', 'book' => 'サピエンス全史'],
                    ['user' => '山田太郎', 'book' => 'サピエンス全史'],
                    ['user' => '高橋健太', 'book' => 'Clean Code'],
                    ['user' => '佐藤美咲', 'book' => '火花'],
                    ['user' => '山田太郎', 'book' => 'コンテナ物語'],
                ],
            ],
            [
                'user' => '田中一郎',
                'likedReviews' => [
                    ['user' => '佐藤美咲', 'book' => '人を動かす'],
                    ['user' => '山田太郎', 'book' => 'リーダブルコード'],
                    ['user' => '山田太郎', 'book' => '坊っちゃん'],
                    ['user' => '山田太郎', 'book' => 'サピエンス全史'],
                    ['user' => '高橋健太', 'book' => 'Clean Code'],
                    ['user' => '佐藤美咲', 'book' => '火花'],
                    ['user' => '山田太郎', 'book' => '火花'],
                    ['user' => '鈴木花子', 'book' => 'FACTFULNESS'],
                    ['user' => '高橋健太', 'book' => 'コンテナ物語'],
                    ['user' => '山田太郎', 'book' => 'コンテナ物語'],
                    ['user' => '鈴木花子', 'book' => 'コンテナ物語'],
                ],
            ],
            [
                'user' => '佐藤美咲',
                'likedReviews' => [
                    ['user' => '鈴木花子', 'book' => 'リーダブルコード'],
                    ['user' => '田中一郎', 'book' => 'リーダブルコード'],
                    ['user' => '山田太郎', 'book' => '坊っちゃん'],
                    ['user' => '鈴木花子', 'book' => '坊っちゃん'],
                    ['user' => '山田太郎', 'book' => 'サピエンス全史'],
                    ['user' => '田中一郎', 'book' => 'Clean Code'],
                    ['user' => '高橋健太', 'book' => 'Clean Code'],
                    ['user' => '鈴木花子', 'book' => 'FACTFULNESS'],
                    ['user' => '田中一郎', 'book' => 'FACTFULNESS'],
                    ['user' => '鈴木花子', 'book' => 'コンテナ物語'],
                ],
            ],
            [
                'user' => '高橋健太',
                'likedReviews' => [
                    ['user' => '鈴木花子', 'book' => 'リーダブルコード'],
                    ['user' => '田中一郎', 'book' => 'リーダブルコード'],
                    ['user' => '鈴木花子', 'book' => '坊っちゃん'],
                    ['user' => '佐藤美咲', 'book' => 'サピエンス全史'],
                    ['user' => '佐藤美咲', 'book' => 'Clean Code'],
                    ['user' => '鈴木花子', 'book' => '嫌われる勇気'],
                    ['user' => '田中一郎', 'book' => '嫌われる勇気'],
                    ['user' => '田中一郎', 'book' => 'FACTFULNESS'],
                    ['user' => '鈴木花子', 'book' => 'コンテナ物語'],
                ],
            ],
        ];

        foreach ($items as $item) {
            $likedReviews = $item['likedReviews'];
            $reviewIds = [];
            foreach ($likedReviews as $likedReview) {
                $reviewerId = User::where('name', $likedReview['user'])->value('id');
                $bookId = Book::where('title', $likedReview['book'])->value('id');
                $reviewId = Review::where('user_id', $reviewerId)
                    ->where('book_id', $bookId)
                    ->value('id');
                $reviewIds[] = $reviewId;
            }

            $user = User::firstWhere('name', $item['user']);

            $user->likedReviews()->syncWithoutDetaching($reviewIds);
        }
    }
}
