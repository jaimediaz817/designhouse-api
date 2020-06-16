<?php

namespace App\Models;

use App\Models\Traits\Likeable;
use Cviebrock\EloquentTaggable\Taggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Design extends Model
{

    // use trait
    use Taggable, Likeable;

    protected $fillable = [
        'user_id',
        'team_id',
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

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
                ->orderBy('created_at', 'asc');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // public function likes()
    // {

    // }

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
