<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewLikeController extends Controller
{
    public function like(Review $review): RedirectResponse
    {
        $review->likedByUsers()->toggle(Auth::id());

        return back();
    }
}
