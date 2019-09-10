<?php

namespace App\Listeners;

use Illuminate\Support\Facades\DB;

class InitUser
{
    public function __construct()
    {
    }

    public function handle($event)
    {
        $userRow = $event->data();

        DB::table('user_extend')->insert([
            'user_id' => $userRow->id,
        ]);

        DB::table('user_stat')->insert([
            'user_id' => $userRow->id,
        ]);
    }
}
