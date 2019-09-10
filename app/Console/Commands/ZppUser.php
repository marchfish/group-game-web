<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ZppUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zpp:user';

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
            SELECT COUNT(*) FROM `pro_user`;
            SELECT COUNT(*) FROM `user`;
            37720 - 37711

            SELECT COUNT(DISTINCT `uid`) FROM `pro_loginbinding` WHERE `plugin_id` = 1;
            SELECT COUNT(*) FROM `user_extend` WHERE wx_unionid <> '' OR qq_app_openid <> '' OR wb_app_openid <> '';
            16883 - 16883

            SELECT COUNT(*) FROM `pro_paopao_backlist`;
            45-45
        */
        // user，基本信息，密码
        $is_user = 1;
        // user_extend 第三方登陆绑定
        $is_bind = 1;
        // user_blacklist 用户黑名单
        $is_black = 1;
        // user_group 绑定公司
        $is_group = 1;
        // user_stat 用户统计数据
        $is_stat = 1;

        if ($is_user) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select(['*'])
                    ->from('pro_user')
                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nUserData   = [];
                $nUserIdData = [];

                $map = [
                    -1 => 200,
                    0  => 0,
                    1  => 200,
                ];

                foreach ($oRows as $oRow) {
                    $nUserData[] = [
                        'id'             => $oRow->id,
                        'parent_user_id' => 0,
                        'tel'            => trim($oRow->telephone) ?: null,
                        'nickname'       => trim($oRow->nickname),
                        'password'       => trim($oRow->password),
                        'gender'         => trim($oRow->gender),
                        'age'            => trim($oRow->after),
                        'career'         => trim($oRow->career),
                        'ps'             => trim($oRow->description),
                        'avatar'         => trim($oRow->avatar),
                        'status'         => $map[$oRow->status],
                        'created_at'     => date('Y-m-d H:i:s', $oRow->createtime),
                    ];

                    $nUserIdData[] = [
                        'user_id' => $oRow->id,
                    ];
                }

                $newConn->table('user')->insert($nUserData);
                $newConn->table('user_extend')->insert($nUserIdData);
                $newConn->table('user_stat')->insert($nUserIdData);

                $newConn->commit();

                ++$page;
            }
        }

        if ($is_bind) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select(['*'])
                    ->from('pro_loginbinding')
                    ->where('plugin_id', '=', 1)
                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nUserExtendData = [];

                foreach ($oRows as $oRow) {
                    switch ($oRow->type) {
                    case 'wx':
                        $nUserExtendData[] = [
                            'user_id'       => $oRow->uid,
                            'wx_app_openid' => $oRow->openid,
                            'wx_unionid'    => $oRow->authcode,
                        ];

                        break;
                    case 'qq':
                        $nUserExtendData[] = [
                            'user_id'       => $oRow->uid,
                            'qq_app_openid' => $oRow->authcode,
                        ];

                        break;
                    case 'weibo':
                        $nUserExtendData[] = [
                            'user_id'       => $oRow->uid,
                            'wb_app_openid' => $oRow->authcode,
                        ];

                        break;
                    }
                }

                foreach ($nUserExtendData as $nData) {
                    $newConn->table('user_extend')
                        ->where('user_id', '=', $nData['user_id'])
                        ->update($nData)
                    ;
                }

                $newConn->commit();

                ++$page;
            }
        }

        if ($is_black) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select(['*'])
                    ->from('pro_paopao_backlist')
                    ->orderBy('uid', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nUserBlackData = [];

                foreach ($oRows as $oRow) {
                    $nUserBlackData[] = [
                        'zpp_user_id' => $oRow->uid,
                        'pp_user_id'  => $oRow->blackid,
                        'created_at'  => date('Y-m-d H:i:s', $oRow->createtime),
                    ];
                }

                $newConn->table('user_blacklist')->insert($nUserBlackData);

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
                    ->from('pro_paopao_group_boss')
                    ->orderBy('uid', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nUserGroupData = [];

                foreach ($oRows as $oRow) {
                    $nUserGroupData[] = [
                        'user_id'    => $oRow->uid,
                        'group_id'   => $oRow->gid,
                        'status'     => 200,
                        'created_at' => $oRow->createtime,
                    ];
                }

                $newConn->table('user_group')->insert($nUserGroupData);

                $newConn->commit();

                ++$page;
            }
        }

        if ($is_stat) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select(['*'])
                    ->from('pro_paopao_usercount')
                    ->orderBy('uid', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nUserStatData = [];

                foreach ($oRows as $oRow) {
                    $nUserStatData[] = [
                        'user_id' => $oRow->uid,
                        // 'deposit'             => $oRow->cash,
                        // 'withdrawing_deposit' => $oRow->waitbank,
                        // 'withdrawed_deposit'  => $oRow->tobank,
                        'review_star'  => $oRow->stocksum,
                        'review_num'   => $oRow->rateesum,
                        'zpp_pay'      => $oRow->cost,
                        'zpp_savetime' => $oRow->duration,
                        'zpp_lb'       => $oRow->lb,
                        // 'pp_salary'           => $oRow->balance,
                        'pp_exp' => $oRow->score,
                        'pp_km'  => $oRow->total_km,
                    ];
                }

                foreach ($nUserStatData as $nUserStatRow) {
                    $newConn->table('user_stat')
                        ->where('user_id', '=', $nUserStatRow['user_id'])
                        ->update($nUserStatRow)
                    ;
                }

                $newConn->commit();

                ++$page;
            }
        }

        echo '总耗时：' . (time() - $t1) . ' 秒' . PHP_EOL;
    }
}
