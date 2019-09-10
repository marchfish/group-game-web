<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Group;

class GroupUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'group:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $t1      = time();
        $oldConn = \Illuminate\Support\Facades\DB::connection('zpp');
        $newConn = \Illuminate\Support\Facades\DB::connection('zpp_new');

        /*
            SELECT COUNT(*) from pro_paopao_group;
            select count(*) from `group`;
            112 - 112
        */
        $is_group_user   = 1;
        $is_group        = 1;
        // $is_group_salary = 0;

        if ($is_group_user) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select([
                        'gm.*',
                        'gt.money AS salary',
                        'gt.payment AS payed_salary',
                        'gt.amount AS order_num',
                    ])
                    ->from('pro_paopao_group_member AS gm')
                    ->leftJoin('pro_paopao_group_trade AS gt', function ($join) {
                        $join
                            ->on('gt.gid', '=', 'gm.gid')
                            ->on('gt.uid', '=', 'gm.uid')
                            ->where('gt.status', '=', 1)
                        ;
                    })
                    ->orderBy('gm.id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nGroupUserData = [];

                $groupUserMap = [
                    0 => 150,
                    1 => 200,
                ];

                foreach ($oRows as $oRow) {
                    $nGroupUserData[] = [
                        'group_id' => $oRow->gid,
                        'user_id'  => $oRow->uid,
                        'role_id'  => 2,
                        // 'salary'       => $oRow->salary ?: 0,
                        // 'payed_salary' => $oRow->payed_salary ?: 0,
                        // 'order_num'    => $oRow->order_num ?: 0,
                        'status'     => $groupUserMap[$oRow->status],
                        'created_at' => $oRow->createtime,
                    ];
                }

                $newConn->table('group_user')->insert($nGroupUserData);

                $newConn->commit();

                ++$page;
            }
        }

        if ($is_group) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select(['*'])
                    ->from('pro_paopao_group')
                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nGroupData       = [];
                $nGroupAttachData = [];
                $nGroupUserData   = [];

                $map = [
                    -1 => 50,
                    1  => 200,
                    3  => 0,
                ];

                foreach ($oRows as $oRow) {
                    $nGroupData[] = [
                        'id'               => $oRow->id,
                        'user_id'          => $oRow->uid,
                        'no'               => mt_rand(100000, 199999),
                        'name'             => $oRow->name,
                        'logo'             => $oRow->logo,
                        'area'             => $oRow->area,
                        'certificate_code' => $oRow->certificate_code,
                        'address'          => $oRow->address,
                        'description'      => $oRow->description,
                        // 'deposit'             => $oRow->store_money,
                        // 'withdrawing_deposit' => $oRow->store_waitbank,
                        // 'withdrawed_deposit'  => $oRow->store_tobank,
                        // 'frozen_deposit'      => $oRow->store_frozen,
                        'status'     => $map[$oRow->status],
                        'created_at' => $oRow->createtime,
                    ];

                    $nGroupAttachData[] = [
                        'group_id' => $oRow->id,
                        'g_pic1'   => $oRow->certificate_pic,
                    ];

                    $nGroupUserData[] = [
                        'group_id' => $oRow->id,
                        'user_id'  => $oRow->uid,
                    ];
                }

                $newConn->table('group')->insert($nGroupData);
                $newConn->table('group_attach')->insert($nGroupAttachData);

                foreach ($nGroupUserData as $nGroupUserRow) {
                    $newConn->table('group_user')
                        ->updateOrInsert([
                            'group_id' => $nGroupUserRow['group_id'],
                            'user_id'  => $nGroupUserRow['user_id'],
                        ], [
                            'role_id' => 1,
                        ])
                    ;
                }

                $newConn->commit();

                ++$page;
            }
        }

        // if ($is_group_salary) {
        //     $page = 1;
        //     $size = 1000;

        //     while (true) {
        //         $newConn->beginTransaction();

        //         $oRows = $oldConn->query()
        //             ->select([
        //                 '*',
        //             ])
        //             ->from('pro_paopao_group_payment')
        //             ->orderBy('id', 'asc')
        //             ->offset(($page - 1) * $size)
        //             ->limit($size)
        //             ->get()
        //         ;

        //         if ($oRows->isEmpty()) {
        //             $newConn->commit();

        //             break;
        //         }

        //         $nGroupSalaryData = [];

        //         foreach ($oRows as $oRow) {
        //             $nGroupSalaryData[] = [
        //                 'group_id'   => $oRow->gid,
        //                 'user_id'    => $oRow->uid,
        //                 'salary'     => $oRow->pay,
        //                 'created_at' => $oRow->createtime,
        //             ];
        //         }

        //         $newConn->table('group_user_salary_history')->insert($nGroupSalaryData);

        //         $newConn->commit();

        //         ++$page;
        //     }
        // }

        echo '总耗时：' . (time() - $t1) . ' 秒' . PHP_EOL;
    }
}
