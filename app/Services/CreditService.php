<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreditService
{

    public function reserve(User $user, int $amount): void
    {
        DB::transaction(function () use ($user, $amount) {
            $user = User::where('id', $user->id)->lockForUpdate()->first();
            if ($user->credits < $amount) {
                throw new \Exception('Unable to reserve credits: either insufficient balance or concurrent provisioning in progress. Please retry.');
            }

            $user->decrement('credits', $amount);
        });

        Cache::forget('user_credits_left:' . $user->id);
    }

    public function refund(User $user, int $amount): void
    {
        DB::transaction(function () use ($user, $amount) {
            $user = User::where('id', $user->id)->lockForUpdate()->first();
            $user->increment('credits', $amount);
        });

        Cache::forget('user_credits_left:' . $user->id);
    }
}
