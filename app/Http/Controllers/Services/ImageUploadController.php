<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    // function to store image 
    public static function storeImage($image, $directory = "uploads"){
        return "storage/" . $image->store($directory, "public");
    }

    // function to delete image 
    public static function DeleteImage($imageUrl){
        $filePath = 'public/' . str_replace('storage/', '', $imageUrl);
        Storage::delete($filePath);
    }
}
