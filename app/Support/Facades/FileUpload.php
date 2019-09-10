<?php

namespace App\Support\Facades;

use App\Services\FileUpload\FileUploadManager;
use Illuminate\Support\Facades\Facade;

class FileUpload extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FileUploadManager::class;
    }
}
