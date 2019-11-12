<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class Lottery
{
    public static function update()
    {
        $lottery = DB::query()
            ->select([
                '*'
            ])
            ->from('lottery AS l')
            ->where('number', '=', '')
            ->where('created_at', '<', date('Y-m-d 00:00:00', time()))
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$lottery) {
            return '';
        }

        $num1 = mt_rand(0, 9);
        $num2 = mt_rand(0, 9);
        $num3 = mt_rand(0, 9);

        $number = $num1 . $num2 .$num3;

        $jackpot = DB::query()
            ->select([
                '*'
            ])
            ->from('sys_coin AS sc')
            ->limit(1)
            ->get()
            ->first()
        ;

        $userLottery = DB::query()
            ->select([
                'ul.*'
            ])
            ->from('user_lottery AS ul')
            ->where('ul.stage', '=', $lottery->stage)
            ->where('ul.number', '=', $number)
            ->get()
        ;

        DB::beginTransaction();

        foreach ($userLottery as $user) {
            if ($user->user_role_id != 0) {
                DB::table('user_role')
                    ->where('id', '=', $user->user_role_id)
                    ->update([
                        'coin' => DB::raw('`coin` + ' . $jackpot->lottery_coin),
                    ])
                ;

                DB::table('lottery')
                    ->where('id', '=', $lottery->id)
                    ->update([
                        'user_role_id' => $user->user_role_id,
                        'coin'         => $jackpot->lottery_coin,
                    ])
                ;

                DB::table('sys_coin')
                    ->where('id', '=', 1)
                    ->update([
                        'lottery_coin' => 5000000,
                    ])
                ;
            }
        }

        DB::table('lottery')
            ->where('id', '=', $lottery->id)
            ->update([
                'number' => $number,
            ])
        ;

        DB::table('lottery')
            ->insert([
                'stage'  => $lottery->stage + 1,
            ])
        ;

        DB::commit();

        return '';
    }
}
