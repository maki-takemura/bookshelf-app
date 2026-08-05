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
        $users = User::all();
        $books = Book::all();

        $comments = [
            1 => 'あまり満足できませんでした。',
            2 => '少し物足りなく感じました。',
            3 => '普通に楽しめました。',
            4 => 'とても良い内容でした。',
            5 => '非常に満足できる内容でした。',
        ];

        foreach ($books as $book) {
            $reviewCount = random_int(2, 4);

            for ($i = 0; $i < $reviewCount; $i++) {
                $rating = random_int(1, 5);

                Review::create([
                    'user_id' => $users->random()->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating],
                ]);
            }
        }
    }
}
