<?php
namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CompressService {

    private $url = '';
    private $width = 800;
    private $height = 800;
    private $quality = 80;
    private $padNotStretch = false;
    private $isWebp = false;
    private $isFile = false;

    public function __construct($url, $width, $height, $quality, $padNotStretch, $isWebp, $isFile) {
        $this->url = $url;
        $this->width = $width;
        $this->height = $height;
        $this->quality = $quality;
        $this->padNotStretch = $padNotStretch;
        $this->isWebp = $isWebp;
        $this->isFile = $isFile;
    }

    public function compress() {
        $image = $this->processImage();
        
        $filename = $this->getFileName($image);

        return $this->storeFile($image, $filename);
    }

    private function processImage() {
        $img = $this->attemptDownload();

        if (!$this->padNotStretch) {
            $img = $img->resize($this->width, $this->height);
        }
        else {
            $width  = $img->width();
            $height = $img->height();

            $vertical   = (($width < $height) ? true : false);
            $horizontal = (($width > $height) ? true : false);
            $square     = (($width = $height) ? true : false);

            if ($vertical) {
                $newHeight = $this->height;
                $img = $img->resize(null, $newHeight, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else if ($horizontal) {
                $newWidth = $this->width;
                $img = $img->resize($newWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else if ($square) {
                $newWidth = $this->width;
                $img = $img->resize($newWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $img = $img->resizeCanvas($this->width, $this->height, 'center', false, '#ffffff');
        }

        return Image::canvas($img->width(), $img->height(), 'ffffff')
            ->insert($img)
            ->encode($this->isWebp ? 'webp' : 'jpg', $this->quality);
    }

    private function storeFile($file, $filename) {
        Storage::disk('public')->put($filename, $file);
        
        return Storage::disk('public')->url($filename);
    }

    private function attemptDownload() {
        $manager = new ImageManager();
        $try = 1;
        $exception = null;
        while ($try <= 5) {
            try {
                $img = $manager->make($this->url);

                return $img;
            } catch (\Exception $e) {
                $exception = $e;
            }
            $try++;
        }
        
        throw $exception;
    }

    private function getFilename($file) {
        $now = Carbon::now()->toDateTimeString();       
        $hash = md5($file->__toString() . $now);
        $filename = $hash . '.' . ($this->isWebp ? 'webp' : 'jpg');
        return $filename;
    }
}