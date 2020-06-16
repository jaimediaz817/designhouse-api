<?php

namespace App\Repositories\Eloquent;

use App\Models\Design;
use App\Repositories\Contracts\IDesign;
use Illuminate\Http\Request;

class DesignRepository extends BaseRepository implements IDesign
{
    public function model()
    {
        return Design::class;
    }

    public function applyTags($id, array $data)
    {
        $design = $this->find($id);
        $design->retag($data);
    }   
    
    public function addComment($designId, array $data)
    {
        // get the design for with we can to create a comment
        $design = $this->find($designId);

        // Create the commment for the design
        $comment = $design->comments()->create($data);

        return $comment;
    }

    public function like($id)
    {
        $design = $this->model->findOrFail($id);

        if ($design->isLikedByUser(auth()->id())) 
        {
            $design->unlike();
        }
        else
        {
            $design->like();
        }
    }

    // sobreescribiendo el método
    public function isLikedByUser($id)
    {
        $design = $this->model->findOrFail($id);
        return $design->isLikedByUser(auth()->id());
    }

    public function search(Request $request)
    {
        $query = (new $this->model)->newQuery();
        $query->where('is_live', true);

        // return only designs with comments
        if ($request->has_comments) 
        {
            $query->has('comments');
        }

        // return only designs assigned yo teams
        if ($request->has_team) 
        {
            $query->has('team');
        }
        
        // Search title and description for provided string
        // www.site.com?q=hello-world&has_comment=1
        if ($request->q) 
        {
            // echo "si";
            $query->where( function ($q) use ($request){
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        } else 
        {
            // echo "no";
        }
        
        // order the query by likes or latest first
        if ($request->orderBy == 'likes') 
        {
            $query->withCount('likes') // likes_count
                  ->orderByDesc('likes_count');
        }
        else
        {
            $query->latest();
        }

        return $query->get();
    }    
}
