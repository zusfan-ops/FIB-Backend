<?php

namespace App\Services;

use App\Models\Card;
use Illuminate\Support\Carbon;

class Sm2Service
{
    /**
     * Apply the SM-2 algorithm to a card based on a rating.
     *
     * Rating mapping (0-5) used by the clients:
     *   0 = again (lupa total)
     *   1 = lupa tapi hampir ingat
     *   3 = sulit
     *   4 = bagus
     *   5 = mudah
     */
    public function review(Card $card, int $rating): Card
    {
        $quality = max(0, min(5, $rating));
        $ease = $card->ease_factor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $card->ease_factor = max(1.3, $ease);

        if ($quality >= 3) {
            if ($card->state === 'new') {
                $card->state = 'review';
                $card->repetition = 1;
                $card->interval = 1;
            } else {
                $card->repetition++;

                if ($card->repetition <= 1) {
                    $card->interval = 1;
                } elseif ($card->repetition == 2) {
                    $card->interval = 6;
                } else {
                    $card->interval = (int) round($card->interval * $card->ease_factor);
                }
            }
        } else {
            $card->repetition = 0;
            $card->interval = 1;
            $card->lapses++;
            $card->state = 'learning';
        }

        $base = $card->state === 'learning' ? Carbon::now()->addMinutes(10) : Carbon::now();
        $card->due_date = $base->addDays($card->interval)->addMinutes(rand(0, 59));

        return $card;
    }
}
