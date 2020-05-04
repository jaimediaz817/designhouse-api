<?php

namespace App\Http\Controllers\Designs;

use App\Models\Design;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use Illuminate\Support\Str;

class DesignController extends Controller
{
    //
    public function update(Request $request, $id)
    {
        // TODO: question
        //'title' => ['required', 'unique:designs,title,'. $id],

        $design = Design::find($id);

        $this->authorize('update', $design);

        $this->validate($request, [
            'title' => ['required', 'unique:designs,title'],
            'description' => ['required', 'string', 'min:20', 'max:140']
        ]);

        

        $design->update([
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title),
            'is_live' => ! $design->upload_successfule ? false : $request->is_live        
        ]);

        return new DesignResource($design);
        //return response()->json($design, 200);
    }
}
