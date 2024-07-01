<?php

use Illuminate\Support\Facades\Route;

Route::post('/compress', 'ImagesController@compress');
Route::post('/compress/upload', 'ImagesController@uploadAndCompress');
