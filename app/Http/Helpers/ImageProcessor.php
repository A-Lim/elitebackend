<?php 

namespace App\Helpers;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use Carbon\Carbon;

class ImageProcessor {

    public $hashName;
    public $file;

    private $image;
    private $blur = 100;
    private $width = 200;
    private $height = 200;
    
    private $fileName = '';
    private $quality = 70;
    private $moduleFolder = null;
    private $tempFolder = null;

    private const thumbnailWidth = 40;
    private const thumbnailHeight = 40;
    private const extension = 'jpg';
    
    public function __construct(UploadedFile $file, $moduleFolder, $uniqueIdentifier = null) {
        $this->file = $file;
        $this->fileName = md5($uniqueIdentifier.$file->getClientOriginalName());
        $this->image = Image::make($file);

        $this->moduleFolder = $moduleFolder;
        $this->tempFolder = public_path('temp');
        $this->createFolder();
    }

    public function fit($width, $height) {
        $this->width = $width;
        $this->height = $height;

        $this->image->fit($this->width, $this->height);
        return $this;
    }

    public function resize($width, $height) {
        $this->width = $width;
        $this->height = $height;

        $this->image->resize($this->width, $this->height);
        return $this;
    }

    public function thumbnail($width = null, $height = null) {
        // if value is not set, take default
        $this->width = $width ?? self::thumbnailWidth;
        $this->height = $height ?? self::thumbnailHeight;

        return $this->fit($this->width, $this->height);
    }

    public function placeholder($width = null, $height = null, $blur = null) {
        // if value is not set, take default
        $this->width = $width ?? $this->width;
        $this->height = $height ?? $this->height;
        $this->blur = $blur ?? $this->blur;

        $this->fileName .= '-blur';
        
        return $this->fit($this->width, $this->height)
                    ->blur($this->blur);
    }

    public function save($quality = null) {
        $fileName = $this->getFileName();
        $saveDirectory = $this->getSaveDirectory();

        $tmpQuality = $quality ?? $this->quality;
        $tmpSavePath = $this->tempFolder.'/'.$fileName;

        $this->image->save($tmpSavePath, $tmpQuality);
        Storage::putFileAs($saveDirectory, new File($tmpSavePath), $fileName);

        // delete temp file
        unlink($tmpSavePath);
        // returns file location
        return Storage::url($saveDirectory.$fileName);
    }

    private function blur($intensity) {
        $this->image->blur($this->blur);
        return $this;
    }

    private function createFolder() {
        if (!file_exists($this->tempFolder)) 
            mkdir($this->tempFolder, 0777, true);
    }

    private function getSaveDirectory() {
        $today = Carbon::today()->format('Ymd');
        $dimensions = $this->width.'x'.$this->height;
        $directory = 'public/images/'.$this->moduleFolder.'/'.$dimensions.'/'.$today.'/';
        return $directory;
    }

    private function getFileName() {
        return $this->fileName.'.'.self::extension;
    }
}