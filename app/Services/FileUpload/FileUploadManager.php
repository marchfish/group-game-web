<?php

namespace App\Services\FileUpload;

use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class FileUploadManager
{
    private $config;

    private $imageManager;

    private $relativeUploadPath;

    private $absoluteUploadPath;

    /**
     * 允许上传的文件类型, 参照 mime.types 文件.
     *
     * @var array
     */
    private $allowMimeTypes = [
        // 图片
        'image/jpeg' => 'jpeg',
        'image/png'  => 'png',
        /*
        'image/gif'          => 'gif',
        'image/svg+xml'      => 'svg',
        'image/tiff'         => 'tif',
        'image/vnd.wap.wbmp' => 'wbmp',
        'image/webp'         => 'webp',
        'image/x-icon'       => 'ico',
        'image/x-jng'        => 'jng',
        'image/x-ms-bmp'     => 'bmp',
        */
        // 音频
        'audio/x-wav'              => 'wav',
        'application/octet-stream' => 'amr',
        /*
        'audio/midi'        => 'mid',
        'audio/mpeg'        => 'mp3',
        'audio/ogg'         => 'ogg',
        'audio/x-m4a'       => 'm4a',
        'audio/x-realaudio' => 'ra',
        */
        // 视频
        /*
        'video/3gpp'      => '3gpp',
        'video/mp2t'      => 'ts',
        'video/mp4'       => 'mp4',
        'video/mpeg'      => 'mpeg',
        'video/quicktime' => 'mov',
        'video/webm'      => 'webm',
        'video/x-flv'     => 'flv',
        'video/x-m4v'     => 'm4v',
        'video/x-mng'     => 'mng',
        'video/x-ms-asf'  => 'asx',
        'video/x-ms-wmv'  => 'wmv',
        'video/x-msvideo' => 'avi',
        */
    ];

    /**
     * 缩略图尺寸.
     *
     * @var array
     */
    private $thumbSizes = [
        'l' => 320,
        'm' => 128,
        // 's' => 64,
    ];

    public function __construct()
    {
        $this->config             = Config::get('file_upload');
        $this->imageManager       = new ImageManager();
        $this->relativeUploadPath = $this->config['upload_dir'] . '/' . date('Ym');
        $this->absoluteUploadPath = $this->config['root'] . '/' . $this->relativeUploadPath;

        if (!is_dir($this->absoluteUploadPath)) {
            mkdir($this->absoluteUploadPath, 0755, true);
        }
    }

    public function handle($files)
    {
        $files = $this->normalizeFilesArray($files);

        $result = [];

        foreach ($files as $inputName => &$file) {
            try {
                if ($file['error']) {
                    throw new InvalidArgumentException('文件上传失败');
                }

                $mimeType = mime_content_type($file['tmp_name']);

                if (!isset($this->allowMimeTypes[$mimeType])) {
                    throw new InvalidArgumentException('文件类型不允许');
                }

                $ext = '.' . $this->allowMimeTypes[$mimeType];

                do {
                    $name = uniqid();

                    $fullName = $this->absoluteUploadPath($name . $ext);
                } while (file_exists($fullName));

                if (!move_uploaded_file($file['tmp_name'], $fullName)) {
                    throw new InvalidArgumentException('文件移动失败');
                }

                // 如果是图片则缩放生成固定尺寸图
                if (in_array($ext, ['.jpeg', '.png'])) {
                    $image = $this->imageManager->make($fullName);

                    foreach ($this->thumbSizes as $sizeName => $size) {
                        $thumbFullName = $this->absoluteUploadPath($name . '_' . $sizeName . $ext);

                        $isWithAspect = $image->width() > $image->height();

                        $image
                            ->resize(($isWithAspect ? $size : null), ($isWithAspect ? null : $size), function ($constraint) {
                                $constraint->aspectRatio();
                            })
                            ->save($thumbFullName)
                        ;
                    }
                }

                // $result[$inputName] = $this->relativeUploadPath($name . $ext);
                $result[] = $this->relativeUploadPath($name . $ext);
            } catch (InvalidArgumentException $e) {
                // $result[$inputName] = '';
                $result[] = '';
            }
        }

        return $result;
    }

    public function normalizeFilesArray($files)
    {
        $newFiles = [];

        foreach ($files as $name => &$file) {
            if (is_array($file['name'])) {
                $keys = array_keys($file['name']);

                foreach ($keys as &$key) {
                    $newFiles[$name . '.' . $key] = [
                        'name'     => $file['name'][$key],
                        'type'     => $file['type'][$key],
                        'tmp_name' => $file['tmp_name'][$key],
                        'error'    => $file['error'][$key],
                        'size'     => $file['size'][$key],
                    ];
                }
            } else {
                $newFiles[$name] = $file;
            }
        }

        return $newFiles;
    }

    public function relativeUploadPath($name = '')
    {
        return $name ? ($this->relativeUploadPath . '/' . $name) : $this->relativeUploadPath;
    }

    public function absoluteUploadPath($name = '')
    {
        return $name ? ($this->absoluteUploadPath . '/' . $name) : $this->absoluteUploadPath;
    }
}
