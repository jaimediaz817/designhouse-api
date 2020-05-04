<?php

namespace App\Http\Controllers\Designs;

use App\Http\Controllers\Controller;
use App\Jobs\UploadImage;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    //
    public function upload (Request $request)
    {
        // Validate
        $this->validate($request, [
            'image' => ['required', 'mimes:jpeg,gif,bmp,png', 'max:2048']
        ]);

         // get image from request
         $image = $request->file('image');
         $image_path = $image->getPathname();

         // Get the original file name and replace any spaces wirh _
         // Business Cards.png = business_cards.png
         $filename = time(). "_" . preg_replace('/\$+/', '_', strtolower($image->getClientOriginalName()));
            
         // Move the image to the temporary location (tmp)
         $tmp = $image->storeAs('uploads/original', $filename, 'tmp');

         // Create the database record for the design
        $design = auth()->user()->designs()->create([
            'image' => $filename,
            'disk' => config('site.upload_disk')
        ]);

        //return response()->json($design, 200);

        // dispatch a job handle the image manipulation
        $this->dispatch(new UploadImage($design));

        return response()->json($design, 200);
    }
}
