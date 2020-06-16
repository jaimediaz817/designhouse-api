<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Repositories\Contracts\IInvitation;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamsController extends Controller
{
    protected $teams;
    protected $users;
    protected $invitations;

    public function __construct(ITeam $teams, IUser $users, IInvitation $invitations)
    {
        $this->teams = $teams;
        $this->users = $users;
        $this->invitations = $invitations;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:80', 'unique:teams,name']
        ]);

        // create team in database
        $team = $this->teams->create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return new TeamResource($team);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $team = $this->teams->find($id);
        $this->authorize('update', $team);

        $this->validate($request, [
            'name' => ['required', 'string', 'max:80', 'unique:teams,name,' . $id]
        ]);

        $team = $this->teams->update($id, [
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return new TeamResource($team);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function findById($id)
    {
        $team = $this->teams->find($id);
        return new TeamResource($team);
    }

    /**
     * Get the teams that the current user belogns to
     */
    public function fetchUserTeams()
    {
        $team = $this->teams->fetchUserTeams();
        return TeamResource::collection($team);
    }

    public function findBySlug($slug)
    {

    }

    public function removeFromTeam($teamId, $userId)
    {
        // Get the team
        $team = $this->teams->find($teamId);
        $user = $this->users->find($userId);

        // check that the syser is not the owner
        if ($user->isOwnerOfTeam($team)) {
            return response()->json([
                'message' => 'You are the team Owner'
            ], 401);
        }

        // Check thath the person sending the request
        // is either the owner of the team or the person
        // who wants to leave the team
        if (!auth()->user()->isOwnerOfTeam($team) &&
            auth()->id() !== $userId
        ) {
            return response()->json([
                'message' => 'You cannot do this'
            ], 401);
        }

        $this->invitations->removeUserFromTeam($team, $userId);

        return response()->json(['message' => 'Success'], 200);
    }    

}
