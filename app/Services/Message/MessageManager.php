<?php

namespace App\Services\Message;

use App\Support\Facades\Push;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MessageManager
{
    public function __construct()
    {
    }

    public function send(array $query, bool $is_notify = true)
    {
        if (empty($query['user_ids'])) {
            return;
        }

        $id = DB::table('sys_message')->insertGetId([
            'client'  => $query['client'] == 'zpp' ? 1 : 2,
            'title'   => $query['title'],
            'content' => $query['content'],
            'extra'   => isset($query['extra']) ? json_encode($query['extra'], JSON_UNESCAPED_UNICODE) : '',
        ]);

        $data = [];

        foreach ($query['user_ids'] as $user_id) {
            $data[] = [
                'message_id' => $id,
                'user_id'    => $user_id,
            ];
        }

        DB::table('sys_message_user')->insert($data);

        $query['extra']['is_message'] = '1';

        if ($is_notify) {
            Push::notify($query);
        } else {
            Push::message($query);
        }
    }
}
