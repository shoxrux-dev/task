<?php

namespace App\Http\Util;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Ramsey\Uuid\Uuid;

class FileUtil
{
    public static function store(UploadedFile $file, $path): string
    {
        $filename = Uuid::uuid4().'.'.$file->getClientOriginalExtension();
        $file->move($path,$filename);
        return $filename;
    }

    public static function delete(string $file, $path): void
    {
        $filePath = public_path().'/'.$path.'/'.$file;
        if(File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}
