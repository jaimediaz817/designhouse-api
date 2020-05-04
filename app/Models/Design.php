<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Design extends Model
{
    protected $fillable = [
        'user_id',
        'image',
        'title',
        'description',
        'slug',
        'close_to_comment',
        'is_live',
        'upload_successfule',
        'disk'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // TODO: attribute
    public function getImagesAttribute()
    {
        // $thumbnail = Storage::disk($this->disk)
        //                 ->url('uploads/designs/thumbnail/' . $this->image);

        return [
            'thumbnail' => $this->getImagePath('thumbnail'),
            'large' => $this->getImagePath('large'),
            'original' => $this->getImagePath('original'),
        ];
    }

    protected function getImagePath($size)
    {
        return Storage::disk($this->disk)->
                    url("uploads/designs/{$size}/" . $this->image);
    }
}
