<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

function build_qs(array $query): string
{
    return http_build_query($query, '', '&', PHP_QUERY_RFC3986);
}

// 对象转换成数组
function obj2arr($data)
{
    if (is_object($data)) {
        if (method_exists($data, 'toArray')) {
            $data = $data->toArray();
        } else {
            $data = get_object_vars($data);
        }
    }

    if (is_array($data)) {
        return array_map(__FUNCTION__, $data);
    } else {
        return $data;
    }
}

function upload_url(string $path): string
{
    if ($path == '') {
        return $path;
    }

    return Str::startsWith($path, 'http') ? $path : (Config::get('app.url') . '/' . $path);
}

function multi_upload_url(string $path): string
{
    return implode(',', array_map('upload_url', explode(',', $path)));
}

function user_nickname(): string
{
    return 'u_' . mt_rand(100000, 999999);
}

function user_password(string $password): string
{
    return md5('!@a1b2c3' . $password . '3c2b1a@!');
}

function get_gender(string $id_card): int
{
    if (is_numeric($flag = substr($id_card, 16, 1))) {
        return ($flag % 2) ? 1 : 2;
    } else {
        return 0;
    }
}

function calc_share(int $i, int $rate): int
{
    return (int) bcmul($i, bcdiv($rate, 100, 2), 0);
}

function rmb(?int $number): string
{
    return isset($number) ? bcdiv($number, 100, 2) : '';
}

function beauty_date($timestamp)
{
    $timestamp   = strtotime($timestamp);
    static $lang = [
        'before' => '前',
        'day'    => '天',
        'yday'   => '昨天',
        'byday'  => '前天',
        'hour'   => '小时',
        'half'   => '半',
        'min'    => '分钟',
        'sec'    => '秒',
        'now'    => '刚刚',
    ];

    if (!floatval($timestamp)) {
        return '';
    }
    $tformat        = 'Y-n-j H:i';
    $todaytimestamp = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));
    $s              = date($tformat, $timestamp);
    $time           = time() - $timestamp;
    $days           = intval(($todaytimestamp - $timestamp) / 86400);

    if ($timestamp >= $todaytimestamp) {
        if ($time > 3600) {
            $return = intval($time / 3600) . ' ' . $lang['hour'] . $lang['before'];
        } elseif ($time > 1800) {
            $return = $lang['half'] . $lang['hour'] . $lang['before'];
        } elseif ($time > 60) {
            $return = intval($time / 60) . ' ' . $lang['min'] . $lang['before'];
        } elseif ($time > 0) {
//            $return = $time . ' ' . $lang['sec'] . $lang['before'];
            $return = $lang['now'];
        } elseif ($time == 0) {
            $return = $lang['now'];
        } else {
            $return = $s;
        }
    } elseif ($days >= 0 && $days < 7) {
        if ($days == 0) {
            $return = $lang['yday'];
        } elseif ($days == 1) {
            $return = $lang['byday'];
        } else {
            $return = ($days + 1) . ' ' . $lang['day'] . $lang['before'];
        }
    } else {
        $return = $s;
    }

    return $return;
}

// 判断是否成功
function is_success(int $probability_num)
{
    $rand_num = mt_rand(0, 100);

    return $probability_num > $rand_num ? true : false;
}
// 时间差
function time_difference ($old_at, $now_at, $type='minutes')
{
    $now_time = strtotime($now_at);
    $old_time = strtotime($old_at);

    if ($type == 'second') {
        return ($now_time - $old_time);
    }

    if ($type == 'minutes') {
        return ($now_time - $old_time)/60;
    }

    return 0;
}

// 百分比(分子, 分母, 百分号)
function bfb($fz, $fm, $bfh = true)
{
    if ($fm) {
        $result = bcmul(round($fz / $fm, 4), 100, 2);

        return $bfh ? $result . '%' : $result;
    } else {
        return $bfh ? '0.00%' : '0.00';
    }
}
// 分页
function paging(int $current_page, int $last_page, string $url, array $query = []): string
{
    if ($last_page < 2) {
        return '';
    }

    unset($query['page']);

    $qs = '';

    if (!empty($query)) {
        $qs = '&' . build_qs($query);
    }

    $paging = '<ul class="pagination no-margin pull-right">';

    if ($current_page == 1) {
        $paging .= '<li class="disabled"><span>«</span></li>';
    } else {
        $paging .= '<li><a href="' . $url . '?page=' . ($current_page - 1) . $qs . '">«</a></li>';
    }

    if ($last_page < 12) {
        for ($i = 1; $i <= $last_page; ++$i) {
            if ($current_page == $i) {
                $paging .= '<li class="active"><span>' . $i . '</span></li>';
            } else {
                $paging .= '<li><a href="' . $url . '?page=' . $i . $qs . '">' . $i . '</a></li>';
            }
        }
    } else {
        if ($current_page > 6) {
            for ($i = 1; $i <= 2; ++$i) {
                $paging .= '<li><a href="' . $url . '?page=' . $i . $qs . '">' . $i . '</a></li>';
            }

            $paging .= '<li class="disabled"><span>...</span></li>';
        } else {
            for ($i = 1; $i <= 8; ++$i) {
                if ($current_page == $i) {
                    $paging .= '<li class="active"><span>' . $i . '</span></li>';
                } else {
                    $paging .= '<li><a href="' . $url . '?page=' . $i . $qs . '">' . $i . '</a></li>';
                }
            }
        }

        if ($last_page - $current_page < 6) {
            for ($i = $last_page - 7; $i <= $last_page; ++$i) {
                if ($current_page == $i) {
                    $paging .= '<li class="active"><span>' . $i . '</span></li>';
                } else {
                    $paging .= '<li><a href="' . $url . '?page=' . $i . $qs . '">' . $i . '</a></li>';
                }
            }
        } else {
            if ($current_page > 6) {
                for ($i = $current_page - 3; $i <= $current_page + 3; ++$i) {
                    if ($current_page == $i) {
                        $paging .= '<li class="active"><span>' . $i . '</span></li>';
                    } else {
                        $paging .= '<li><a href="' . $url . '?page=' . $i . $qs . '">' . $i . '</a></li>';
                    }
                }
            }

            $paging .= '<li class="disabled"><span>...</span></li>';

            for ($i = $last_page - 1; $i <= $last_page; ++$i) {
                $paging .= '<li><a href="' . $url . '?page=' . $i . $qs . '">' . $i . '</a></li>';
            }
        }
    }

    if ($last_page == $current_page) {
        $paging .= '<li class="disabled"><span>»</span></li>';
    } else {
        $paging .= '<li><a class="wr-next" href="' . $url . '?page=' . ($current_page + 1) . $qs . '">»</a></li>';
    }

    $paging .= '</ul>';

    return $paging;
}

function secToTime($sec, $ishour = false)
{
    if ($sec == 0 || !is_numeric($sec)) {
        return 0;
    }
    $sec = round($sec / 60);

    if ($sec >= 60) {
        $hour = floor($sec / 60);
        $min  = $sec % 60;
        $res  = $hour . ' 小时 ';
        $min != 0 && $res .= $min . ' 分';

        if ($ishour) {
            return $hour;
        }
    } else {
        if ($ishour) {
            return round($sec / 60, 2);
        }
        $res = $sec . ' 分钟';
    }

    return $res;
}

function secToTime1($time)
{
    if (is_numeric($time)) {
        $value = [
            'years'   => 0, 'days' => 0, 'hours' => 0,
            'minutes' => 0, 'seconds' => 0,
        ];

        if ($time >= 31556926) {
            $value['years'] = floor($time / 31556926);
            $time           = ($time % 31556926);
        }

        if ($time >= 86400) {
            $value['days'] = floor($time / 86400);
            $time          = ($time % 86400);
        }

        if ($time >= 3600) {
            $value['hours'] = floor($time / 3600);
            $time           = ($time % 3600);
        }

        if ($time >= 60) {
            $value['minutes'] = floor($time / 60);
            $time             = ($time % 60);
        }
        $value['seconds'] = floor($time);

        return $value['years'] . '年' . $value['days'] . '天' . ' ' . $value['hours'] . '小时' . $value['minutes'] . '分' . $value['seconds'] . '秒';
    } else {
        return false;
    }
}

function formatDateToWeb($query)
{
    $date_from = $query['date_from'] ?? date('Y-m-d', time());
    $date_to   = isset($query['date_to']) ? date('Y-m-d', strtotime($query['date_to'] . ' +1 day')) : date('Y-m-d', strtotime($date_from . ' +1 day'));

    $date_show = date('m月d日', strtotime($date_to . ' -1 day'));

    if (isset($query['date_month'])) {
        if ($query['date_month'] >= 0) {
            $date_from = date('Y-m-01', strtotime(date('Y-m-d', time()) . ' -' . $query['date_month'] . ' month'));
            $date_to   = date('Y-m-d', strtotime(date('Y-m-d', time()) . ' +1 day'));

            if ($query['date_month'] == 1) {
                $date_to = date('Y-m-01', strtotime(date('Y-m-d', time())));

                $date_show = isset($query['date_to']) ? date('m月d日', strtotime($query['date_to'])) : date('m月d日', time());
            }
        } else {
            if ($query['date_month'] == -1) {
                $date_from = date('Y-m-d', strtotime($date_from . ' -1 day'));
                $date_to   = date('Y-m-d', time());
                $date_show = date('m月d日', strtotime($date_from));
            }
        }
    }

    $ts_from = strtotime($date_from);
    $ts_to   = strtotime($date_to);

    if (isset($query['date_format']) && !isset($query['date_month'])) {
        $date_show = '';

        if (date('Y', $ts_from) != date('Y', time())) {
            $date_show = date('Y年', $ts_from);
        }

        if ($query['date_format'] == 'month') {
            $date_show .= date('m月', $ts_from);
        } else {
            $date_show .= date('m月d日', strtotime($date_to . ' -1 day'));
        }
    }

    return $data = [
              'date_show' => $date_show,
              'date_from' => $date_from,
              'date_to'   => $date_to,
              'ts_from'   => $ts_from,
              'ts_to'     => $ts_to,
           ];
}
