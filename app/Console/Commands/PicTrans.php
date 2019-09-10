<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Exception\NotReadableException;

class PicTrans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pic:trans';

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

        $is_topic_pic   = 0;
        $is_user_avatar = 0;
        $is_group_logo  = 1;

        if ($is_topic_pic) {
            $page = 1;
            $size = 100;
            $one_upload = 'sdf/';
            while (true) {
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
                    break;
                }
                $imageManager = new ImageManager();

                foreach ($oRows as $da) {
                    if (isset($da->ext) && $da->ext != '') {
                        $ext = json_decode($da->ext);

                        foreach ($ext->image as $k => $v) {
                            $data       = $oldConn->table('pro_quan_attach')->where('id', '=', $v)->get()->first();
                            $arr        = explode('.', $data->url);
                            $arr1       = explode('/', $data->url);
                            $thumbSizes = [
                                'l' => 320,
                                'm' => 128,
                                // 's' => 64,
                            ];

                            try {
                                // $image = $imageManager->make('http://paopao.myncic.com/upload/201906/2019061442975264.jpg');
                                $image = $imageManager->make('http://paopao.myncic.com/' . $data->url);

                                foreach ($thumbSizes as $sizeName => $size) {
                                    $thumbFullName = public_path($one_upload . $arr[0] . '_' . $sizeName . '.' . $arr[1]);
                                    $path          = public_path($one_upload . $arr1[0] . '/' . $arr1[1]);
                                    File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
                                    $isWithAspect = $image->width() > $image->height();

                                    $image
                                        ->resize(($isWithAspect ? $size : null), ($isWithAspect ? null : $size), function ($constraint) {
                                            $constraint->aspectRatio();
                                        })
                                        ->save($thumbFullName)
                                    ;
                                }
                            } catch (NotReadableException $e) {
                                echo 'not readable';
                            }
                        }
                    }
                }

                ++$page;
            }
        }
        if ($is_user_avatar) {
            $page = 1;
            $size = 1000;
            $one_upload = 'avatar/';
            while (true) {
                $oRows = $oldConn->query()
                    ->select([
                        '*',
                    ])
                    ->from('pro_user')
                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    break;
                }
                $imageManager = new ImageManager();

                foreach ($oRows as $da) {
                    if (isset($da->avatar) && $da->avatar != '') {



                            $arr        = explode('.', $da->avatar);
                            $arr1       = explode('/', $da->avatar);
                            $thumbSizes = [
                                'l' => 320,
                                'm' => 128,
                                // 's' => 64,
                            ];

                            try {
                                // $image = $imageManager->make('http://paopao.myncic.com/upload/201906/2019061442975264.jpg');
                                $image = $imageManager->make('http://paopao.myncic.com/' . $da->avatar);

                                foreach ($thumbSizes as $sizeName => $size) {
                                    $thumbFullName = public_path($one_upload . $arr[0] . '_' . $sizeName . '.' . $arr[1]);
                                    $path          = public_path($one_upload . $arr1[0] . '/' . $arr1[1]);
                                    File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
                                    $isWithAspect = $image->width() > $image->height();

                                    $image
                                        ->resize(($isWithAspect ? $size : null), ($isWithAspect ? null : $size), function ($constraint) {
                                            $constraint->aspectRatio();
                                        })
                                        ->save($thumbFullName)
                                    ;
                                }
                            } catch (NotReadableException $e) {
                                echo $da->id;
                                echo 'not readable';
                            }

                    }
                }

                ++$page;
            }
        }
        if ($is_group_logo) {
            $page = 1;
            $size = 1000;
            $one_upload = 'logo/';
            while (true) {
                $oRows = $oldConn->query()
                    ->select([
                        '*',
                    ])
                    ->from('pro_paopao_group')
                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $size)
                    ->limit($size)
                    ->get()
                ;

                if ($oRows->isEmpty()) {
                    break;
                }
                $imageManager = new ImageManager();

                foreach ($oRows as $da) {
                    if (isset($da->logo) && $da->logo != '') {



                        $arr        = explode('.', $da->logo);
                        $arr1       = explode('/', $da->logo);
                        $thumbSizes = [
                            'l' => 320,
                            'm' => 128,
                            // 's' => 64,
                        ];

                        try {
                            // $image = $imageManager->make('http://paopao.myncic.com/upload/201906/2019061442975264.jpg');
                            $image = $imageManager->make('http://paopao.myncic.com/' . $da->logo);

                            foreach ($thumbSizes as $sizeName => $size) {
                                $thumbFullName = public_path($one_upload . $arr[0] . '_' . $sizeName . '.' . $arr[1]);
                                $path          = public_path($one_upload . $arr1[0] . '/' . $arr1[1]);
                                File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
                                $isWithAspect = $image->width() > $image->height();

                                $image
                                    ->resize(($isWithAspect ? $size : null), ($isWithAspect ? null : $size), function ($constraint) {
                                        $constraint->aspectRatio();
                                    })
                                    ->save($thumbFullName)
                                ;
                            }
                        } catch (NotReadableException $e) {
                            echo 'not readable';
                        }

                    }
                }

                ++$page;
            }
        }

        echo '总耗时：' . (time() - $t1) . ' 秒' . PHP_EOL;
    }

}
