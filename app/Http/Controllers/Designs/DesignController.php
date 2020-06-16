<?php

namespace App\Http\Controllers\Designs;

use App\Models\Design;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Repositories\Contracts\IDesign;
use App\Repositories\Eloquent\Criteria\{
    EagerLoad,
    ForUser,
    IsLive,
    LatestFirst
};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesignController extends Controller
{
    protected $designs;

    public function __construct(IDesign $designs)
    {
        $this->designs = $designs;
    }

    public function index()
    {
        //$designs = $this->designs->all();        
        $designs = $this->designs->withCriteria([
            new LatestFirst(),
            new IsLive(),
            new ForUser(3),
            new EagerLoad(['user', 'comments'])
        ])->all();
        return DesignResource::collection($designs);
    }

    public function findBySlug($slug)
    {
        $design = $this->designs->withCriteria([
            new IsLive(),
            new EagerLoad(['user', 'comments'])
        ])->findWhereFirst('slug', $slug);
        return new DesignResource($design);
    }

    public function findDesign($id)
    {
        $design = $this->designs->find($id);
        return new DesignResource($design);
    }

    //
    public function update(Request $request, $id)
    {
        // TODO: question
        //'title' => ['required', 'unique:designs,title,'. $id],



        $design = $this->designs->find($id); //Design::findOrFail($id);
        $this->authorize('update', $design);

        $this->validate($request, [
            'title' => ['required', 'unique:designs,title,' . $id],
            'description' => ['required', 'string', 'min:20', 'max:140'],
            'tags' => ['required'],
            'team' => ['required_if:assign_to_team,true']
        ]);


        // $design->update([
        $design = $this->designs->update($id, [
            'team_id' => $request->team,
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title),
            'is_live' => ! $design->upload_successfule ? false : $request->is_live
        ]);

        // Mecanismo de tags:
        // $design->retag($request->tags);
        // $des = $this->designs->find($id);
        // $des->retag($request->tags);
        //$this->designs->applyTags($id, $request->tags);
        $this->designs->applyTags($id, $request->tags);
        return new DesignResource($design);
        //return response()->json($design, 200);
    }

    public function destroy($id)
    {
        $design = $this->designs->find($id);//Design::findOrFail($id);
        $this->authorize('delete', $design);

        // delete the files associated to the record
        foreach(['thumbnail', 'large', 'original'] as $size)
        {
            if (Storage::disk($design->disk)
                    ->exists("uploads/designs/{$size}/" . $design->image)) 
            {
                Storage::disk($design->disk)->delete("uploads/designs/{$size}/" . $design->image);
            }
        }

        $this->designs->delete($id); //$design->delete();

        return response()->json(['message' => 'Record deleted'], 200);
    }



    // LIKES
    public function like($id)
    {
        $this->designs->like($id);
        return response()->json(['message'=> 'Successful'], 200);
    }

    public function checkIfUserHasLiked($designId)
    {
        $isLiked = $this->designs->isLikedByUser($designId);
        return response()->json(['liked' => $isLiked]);
    }


    // SEARCHS
    public function search(Request $request)
    {
        $designs = $this->designs->search($request);
        return DesignResource::collection($designs);
    }

    public function getForTeam($teamId)
    {
        $design = $this->designs->withCriteria([
            new IsLive()
        ])->findWhere('team_id', $teamId);
        return DesignResource::collection($design);        
    }

    public function getForUser($userId)
    {
        $design = $this->designs
            //->withCriteria([new IsLive()])
            ->findWhere('user_id', $userId);
        return DesignResource::collection($design);        
    }    

    public function userOwnsDesign($id)
    {
        $design = $this->designs->withCriteria(
            [ new ForUser(auth()->id())]
        )->findWhereFirst('id', $id);

        return new DesignResource($design);
    }
    
}
