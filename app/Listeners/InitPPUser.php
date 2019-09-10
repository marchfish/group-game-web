<?php

namespace App\Listeners;

use Illuminate\Support\Facades\DB;

class InitPPUser
{
    public function __construct()
    {
    }

    public function handle($event)
    {
        $ppUserRow = $event->data();

        DB::table('pp_attach')->insert([
            'user_id' => $ppUserRow->user_id,
        ]);
    }
}
