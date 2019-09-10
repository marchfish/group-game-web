<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TopicController extends Controller
{
    /**
     *微圈列表.
     */
    public function index()
    {
        try {
            $query = Request::all();

            $model = DB::query()
                ->select([
                    't.*',
                    'u.nickname',
                    'u.avatar',
                ])
                ->from('topic AS t')
                ->join('user AS u', function ($join) {
                    $join
                        ->on('u.id', '=', 't.user_id')
                    ;
                });


            if (isset($query['search_uid']) && $query['search_uid'] != 0) {
                $model->where('t.user_id', '=', $query['search_uid']);
            }

            if (isset($query['search_text'])) {
                $model->where('t.text', 'like', '%' . $query['search_text'] . '%');
            }




            $paginate = $model
                ->orderByDesc('created_at')
                ->paginate(10);

            foreach ($paginate->items() as $item) {
                $ext = [];

                if ($item->ext) {
                    $exts = json_decode($item->ext, true);

                    foreach ($exts as $kk => $vv) {
                        foreach ($vv as $kkk => $vvv) {
                            if (isset($vvv)) {
                                $ext[$kk][$kkk]['url'] = upload_url($vvv);

                                if ($kk = 'image') {
                                    $arr = explode('.', $vvv);

                                    if (file_exists(public_path($arr[0] . '_l' . '.' . $arr[1]))) {
                                        $ext[$kk][$kkk]['url']    = upload_url($arr[0] . '_l.' . $arr[1]);
                                        $ext[$kk][$kkk]['thumb']  = upload_url($arr[0] . '_m.' . $arr[1]);
                                        $info                     = getimagesize(public_path($arr[0] . '_l' . '.' . $arr[1]));
                                        $ext[$kk][$kkk]['width']  = $info[0];
                                        $ext[$kk][$kkk]['height'] = $info[1];
                                    } else {
                                        $ext[$kk][$kkk]['width']  = 0;
                                        $ext[$kk][$kkk]['height'] = 0;
                                    }
                                }
                            }
                        }
                    }
                }
//                dd($ext);
                $item->ext = $ext;

                $item->avatar   = upload_url($item->avatar != '' ? $item->avatar : 'wechat/img/ss.png');
                $item->showdate = beauty_date($item->created_at);
                //获取评论数
                $row = DB::query()
                    ->select([
                        DB::raw('COUNT(`id`) AS count'),
                    ])
                    ->from('topic_comment')
                    ->where('topic_id', '=', $item->id)
                    ->get()
                    ->first();

                $item->comment_count = $row->count;

                //获取点赞数
                $row1 = DB::query()
                    ->select([
                        DB::raw('COUNT(`id`) AS count'),
                    ])
                    ->from('topic_agree')
                    ->where('topic_id', '=', $item->id)
                    ->get()
                    ->first();


                $item->agree_count = $row1->count;
            }

            return Response::view('admin/topic/index', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *举报主题列表.
     */
    public function reportTopic()
    {
        try {
            $query = Request::all();

            $model = DB::query()
                ->select([
                    't.*',
                    'u.nickname',
                    'u.avatar',
                    'r.content',
                ])
                ->from('topic AS t')
                ->join('user AS u', function ($join) {
                    $join
                        ->on('u.id', '=', 't.user_id')
                    ;
                })
                ->join('topic_report AS r', function ($join) {
                    $join
                        ->on('r.topic_id', '=', 't.id')
                    ;
                })
                ->where('t.report_num', '>', 0)
            ;


            if (isset($query['search_uid']) && $query['search_uid'] != 0) {
                $model->where('t.user_id', '=', $query['search_uid']);
            }

            if (isset($query['search_text'])) {
                $model->where('t.text', 'like', '%' . $query['search_text'] . '%');
            }




            $paginate = $model
                ->orderByDesc('created_at')
                ->paginate(10);

            foreach ($paginate->items() as $item) {
                $ext = [];

                if ($item->ext) {
                    $exts = json_decode($item->ext, true);

                    foreach ($exts as $kk => $vv) {
                        foreach ($vv as $kkk => $vvv) {
                            if (isset($vvv)) {
                                $ext[$kk][$kkk]['url'] = upload_url($vvv);

                                if ($kk = 'image') {
                                    $arr = explode('.', $vvv);

                                    if (file_exists(public_path($arr[0] . '_l' . '.' . $arr[1]))) {
                                        $ext[$kk][$kkk]['url']    = upload_url($arr[0] . '_l.' . $arr[1]);
                                        $ext[$kk][$kkk]['thumb']  = upload_url($arr[0] . '_m.' . $arr[1]);
                                        $info                     = getimagesize(public_path($arr[0] . '_l' . '.' . $arr[1]));
                                        $ext[$kk][$kkk]['width']  = $info[0];
                                        $ext[$kk][$kkk]['height'] = $info[1];
                                    } else {
                                        $ext[$kk][$kkk]['width']  = 0;
                                        $ext[$kk][$kkk]['height'] = 0;
                                    }
                                }
                            }
                        }
                    }
                }
//                dd($ext);
                $item->ext = $ext;

                $item->avatar   = upload_url($item->avatar != '' ? $item->avatar : 'wechat/img/ss.png');
                $item->showdate = beauty_date($item->created_at);
                //获取评论数
                $row = DB::query()
                    ->select([
                        DB::raw('COUNT(`id`) AS count'),
                    ])
                    ->from('topic_comment')
                    ->where('topic_id', '=', $item->id)
                    ->get()
                    ->first();

                $item->comment_count = $row->count;

                //获取点赞数
                $row1 = DB::query()
                    ->select([
                        DB::raw('COUNT(`id`) AS count'),
                    ])
                    ->from('topic_agree')
                    ->where('topic_id', '=', $item->id)
                    ->get()
                    ->first();


                $item->agree_count = $row1->count;
            }

            return Response::view('admin/topic/index', [
                'paginate' => $paginate,
                'isreport' => 1,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *举报评论列表.
     */
    public function reportComment()
    {
        try {
            $query = Request::all();

            $model = DB::query()
                ->select([
                    'c.*',
                    'u1.nickname as rater',
                    'u2.nickname as ratee',
                    'r.content as rcontent',
                ])
                ->from('topic_comment as c')
                ->join('user AS u1', function ($join) {
                    $join
                        ->on('u1.id', '=', 'c.rater_user_id')
                    ;
                })
                ->join('user AS u2', function ($join) {
                    $join
                        ->on('u2.id', '=', 'c.ratee_user_id')
                    ;
                })
                ->join('topic_report AS r', function ($join) {
                    $join
                        ->on('r.comment_id', '=', 'c.id')
                    ;
                })
                ->where('c.isreport', '>', 0);

            if (isset($query['reter'])) {
                $model->where('u1.nickname', 'like', '%' . $query['reter'] . '%');
            }

            if (isset($query['retee'])) {
                $model->where('u2.nickname', 'like', '%' . $query['retee'] . '%');
            }

            $paginate = $model
                ->orderByDesc('created_at')
                ->paginate(10);

            foreach ($paginate->items() as $item) {
                $item->rater    = $item->rater ? $item->rater : '匿名';
                $item->ratee    = $item->ratee ? $item->ratee : '匿名';
                $item->showdate = beauty_date($item->created_at);
            }

            return Response::view('admin/topic/comment', [
                'paginate' => $paginate,
                'isreport' => 1,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *删除微圈主题.
     * */
    public function TopicDelete()
    {
        try {
            $query     = Request::all();
            $validator = Validator::make($query, [
                'tid' => 'required',
            ], [
                'tid.required' => '被举报的主题id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $model = DB::query()
                ->select(['*'])
                ->from('topic')
                ->where('id', '=', $query['tid']);

            if (isset($query['isreport']) && $query['isreport'] > 0) {
                $model->where('report_num', '>', 0);
            }

            $topic = $model->orderByDesc('created_at')
                ->get()
                ->first();

            if (!$topic) {
                throw new InvalidArgumentException('被举报的主题不存在！', 400);
            }

            DB::table('topic')
                ->where('id', '=', $query['tid'])
                ->delete();

            DB::table('topic_agree')
                ->where('topic_id', '=', $query['tid'])
                ->delete();

            DB::table('topic_comment')
                ->where('topic_id', '=', $query['tid'])
                ->delete();

            if (isset($query['isreport']) && $query['isreport'] > 0) {
                DB::table('topic_report')
                    ->where('topic_id', '=', $query['tid'])
                    ->delete();
            }

            return Response::json([
                'code'    => 200,
                'message' => '删除成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *删除评论.
     * */
    public function commentDelete()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'comment_id' => ['required'],
            ], [
                'comment_id.required' => 'comment_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有需要删除的被举报的评论
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('topic_comment')
                ->where('id', '=', $query['comment_id'])
                ->where('isreport', '>', 0)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于被举报的评论', 400);
            }

            $res = DB::table('topic_comment')
                ->where('id', '=', $row->id)
                ->delete();

            if (!$res) {
                throw new InvalidArgumentException('操作失败，网络错误！', 400);
            }

            DB::table('topic')
                ->where('id', '=', $row->topic_id)
                ->update([
                    'comment_num' => DB::raw(' `comment_num` -1 '),
                ]);

            DB::table('topic_news')
                ->where('comment_id', '=', $row->id)
                ->delete();

            DB::table('topic_report')
                ->where('comment_id', '=', $row)
                ->delete();

            return Response::json([
                'code'    => 200,
                'message' => '成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *禁显.
     * */
    public function disabled()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'comment_id' => ['required'],
            ], [
                'comment_id.required' => 'comment_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有需要删除的被举报的评论
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('topic_comment')
                ->where('id', '=', $query['comment_id'])
                ->where('isreport', '>', 0)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于被举报的评论', 400);
            }

            // 更改评论状态
            DB::table('topic_comment')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 0,
                ])
            ;

            return Response::json([
                'code'    => 200,
                'message' => '成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *解禁.
     * */
    public function removeDisabled()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'comment_id' => ['required'],
            ], [
                'comment_id.required' => 'comment_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有存在被删除的评论
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('topic_comment')
                ->where('id', '=', $query['comment_id'])
                ->where('status', '=', 0)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于被删除的评论', 400);
            }

            // 更改评论状态
            DB::table('topic_comment')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 200,
                ])
            ;

            return Response::json([
                'code'    => 200,
                'message' => '成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
