<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TopicData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'topic:data';

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
        /*
            TRUNCATE `topic`;
            TRUNCATE `topic_agree`;
            TRUNCATE `topic_comment`;
       */
        $t1      = time();
        $oldConn = \Illuminate\Support\Facades\DB::connection('zpp');
        $newConn = \Illuminate\Support\Facades\DB::connection('zpp_new');

        /*
            SELECT COUNT(*) from pro_paopao_group;
            select count(*) from `group`;
            112 - 112
        */
        $is_topic   = 1;
        $is_comment = 1;
        $is_agree   = 1;

        if ($is_topic) {
            $page = 1;
            $size = 497;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select([
                        '*',
                    ])
                    ->from('pro_quan_topic')
                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $nTopicData = [];

                foreach ($oRows as $da) {
                    $ext = '';

                    if (isset($da->ext) && $da->ext != '') {
                        $ext = json_decode($da->ext);

                        foreach ($ext->image as $k => $v) {
                            $data           = $oldConn->table('pro_quan_attach')->where('id', '=', $v)->get()->first();
                            $ext->image[$k] = $data->url;
                        }
                        $ext = json_encode($ext);
                    }

                    $nTopicData[] = [
                        'id'          => $da->id,
                        'user_id'     => $da->uid,
                        'text'        => $da->txt,
                        'ext'         => $ext,
                        'agree_num'   => $da->agree,
                        'comment_num' => $da->comment,
                        'area'        => $da->area,
                        'is_private'  => $da->private,
                        'created_at'  => date('Y-m-d H:i:s', $da->dateline),
                        'status'      => 200,
                    ];
                }

                $newConn->table('topic')->insert($nTopicData);

                $newConn->commit();

                ++$page;
            }
        }

        if ($is_comment) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select(['*'])
                    ->from('pro_quan_comment')
                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $CommentData = [];
                $newsData    = [];

                $map = [
                    -1 => 50,
                    1  => 200,
                    3  => 0,
                ];

                foreach ($oRows as $da) {
                    $CommentData[] = [
                        'id'            => $da->id,
                        'rater_user_id' => $da->raterid,
                        'ratee_user_id' => $da->rateeid,
                        'parent_id'     => $da->parentid,
                        'content'       => $da->content,
                        'topic_id'      => $da->tid,
                        'created_at'    => date('Y-m-d H:i:s', $da->dateline),
                    ];

                    $newsData[] = [
                        'topic_id'     => $da->tid,
                        'content'      => $da->content,
                        'news_user_id' => $da->rateeid,
                        'user_id'      => $da->raterid,
                        'comment_id'   => $da->id,
                        'type'         => 'pl',
                        'status'       => '2',
                    ];
                }

                $newConn->table('topic_comment')->insert($CommentData);
                $newConn->table('topic_news')->insert($newsData);


                $newConn->commit();

                ++$page;
            }
        }

        if ($is_agree) {
            $page = 1;
            $size = 1000;

            while (true) {
                $newConn->beginTransaction();

                $oRows = $oldConn->query()
                    ->select(['*'])
                    ->from('pro_quan_praise')
                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    $newConn->commit();

                    break;
                }

                $CommentData = [];
                $newsData    = [];

                foreach ($oRows as $da) {
                    $CommentData[] = [
                        'id'         => $da->id,
                        'user_id'    => $da->uid,
                        'topic_id'   => $da->tid,
                        'created_at' => date('Y-m-d H:i:s', $da->dateline),
                    ];

                    $newsData[] = [
                        'topic_id'     => $da->tid,
                        'content'      => '',
                        'news_user_id' => $da->authorid,
                        'user_id'      => $da->uid,
                        'agree_id'     => $da->id,
                        'type'         => 'dz',
                        'status'       => '2',
                    ];
                }

                $newConn->table('topic_agree')->insert($CommentData);
                $newConn->table('topic_news')->insert($newsData);


                $newConn->commit();

                ++$page;
            }
        }

        echo '总耗时：' . (time() - $t1) . ' 秒' . PHP_EOL;
    }
}
