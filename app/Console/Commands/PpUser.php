<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PpUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pp:user';

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
            SELECT COUNT(*) FROM `pro_paopao_auth`;
            SELECT COUNT(*) FROM `pp_user`;.
            4406 - 4405, uid=12, 脏数据，pro_user 中没有记录
         */
        // pp_user
        $is_pp_user = 1;
        $is_review  = 1;

        if ($is_pp_user) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select([
                        'pa.*',
                        'u.telephone AS tel',
                        'u.description',
                        'u.avatar',
                        'u.createtime',
                    ])
                    ->from('pro_paopao_auth AS pa')
                    ->join('pro_user AS u', function ($join) {
                        $join
                            ->on('u.id', '=', 'pa.uid')
                        ;
                    })
                    ->orderBy('pa.uid', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nPpUserData  = [];
                $nPpAttchData = [];

                $map = [
                    -1 => 150,
                    0  => 100,
                    1  => 200,
                    2  => 50,
                    3  => 0,
                ];

                foreach ($oRows as $oRow) {
                    $nPpUserData[] = [
                        'user_id'     => $oRow->uid,
                        'tel'         => trim($oRow->tel),
                        'realname'    => trim($oRow->realname),
                        'area'        => trim($oRow->area),
                        'near'        => $oRow->near,
                        'idcard'      => $oRow->idcard,
                        'gender'      => get_gender($oRow->idcard),
                        'dl'          => trim($oRow->dl),
                        'contact_man' => trim($oRow->contact_man),
                        'contact_tel' => trim($oRow->contact_tel),
                        'intro'       => trim($oRow->description),
                        'avatar'      => trim($oRow->avatar),
                        'is_work'     => 0,
                        'is_quick'    => 0,
                        'status'      => $map[$oRow->status],
                        'created_at'  => date('Y-m-d H:i:s', $oRow->createtime),
                    ];

                    $pics = explode(',', $oRow->photo);

                    $nPpAttchData[] = [
                        'user_id' => $oRow->uid,
                        'pp_pic1' => $pics[0] ?? '',
                        'pp_pic2' => $pics[1] ?? '',
                        'pp_pic3' => $pics[3] ?? '',
                        'pp_pic4' => $pics[2] ?? '',
                        'pp_pic5' => $pics[4] ?? '',
                        'pp_pic6' => trim($oRow->wxpay),
                    ];
                }

                $newConn->table('pp_user')->insert($nPpUserData);
                $newConn->table('pp_attach')->insert($nPpAttchData);

                $newConn->commit();

                ++$page;
            }
        }

        if ($is_review) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select([
                        '*',
                    ])
                    ->from('pro_paopao_comment')
                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nReviewData = [];

                foreach ($oRows as $oRow) {
                    $nReviewData[] = [
                        'order_id'      => 0,
                        'rater_user_id' => $oRow->raterid,
                        'ratee_user_id' => $oRow->rateeid,
                        'area'          => $oRow->area,
                        'star'          => $oRow->score,
                        'content'       => $oRow->message,
                        'created_at'    => date('Y-m-d H:i:s', $oRow->createtime),
                    ];
                }

                $newConn->table('order_review')->insert($nReviewData);

                $newConn->commit();

                ++$page;
            }
        }

        echo '总耗时：' . (time() - $t1) . ' 秒' . PHP_EOL;
    }
}
