<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompressService;

class ImagesController extends Controller
{
    public function compress(Request $request) {
        $request->validate([
            'url' => 'required|active_url',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'quality' => 'required|numeric',
            'padNotStretch' => 'required|bool',
            'isWebp' => 'required|bool'
        ]);

        $compressor = new CompressService($request->url, $request->width, 
            $request->height, $request->quality, $request->padNotStretch, $request->isWebp, false);

        return $compressor->compress();
    }

    public function uploadAndCompress(Request $request) {
        $request->validate([
            'image' => 'required|image',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'quality' => 'required|numeric',
            'padNotStretch' => 'required|bool',
            'isWebp' => 'required|bool'
        ]);

        $compressor = new CompressService($request->file('image'), $request->width, 
            $request->height, $request->quality, $request->padNotStretch, $request->isWebp, true);

        return $compressor->compress();
    }
}
