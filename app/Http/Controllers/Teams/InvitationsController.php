<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Mail\SendInvitationToJoinTeam;
use App\Models\Team;
use App\Repositories\Contracts\IInvitation;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvitationsController extends Controller
{
    protected $invitations;
    protected $teams;
    protected $users;

    public function __construct(
        IInvitation $invitations,
        ITeam $teams,
        IUser $users
    )
    {
        $this->invitations = $invitations;
        $this->teams       = $teams;
        $this->users        = $users;
    }

    public function invite (Request $request, $teamId)
    {
        // get the Team
        $team = $this->teams->find($teamId);

        // Validate Request
        $this->validate($request, [
            'email' => ['required', 'email']
        ]);

        $user = auth()->user();

        // Check if the user ows the team
        if ( ! $user->isOwnerOfTeam($team)) 
        {
            return response()->json(
                ['email' => 'You are not the team owner'],
                401
            );
        }

        // Check if the email has a pending invitation
        if ( $team->hasPendingInvite($request->email) )
        {
            return response()->json(
                ['email' => 'Email already has a pending invite'],
                422
            );            
        }

        // get the recipient by email
        $recipient = $this->users->findByEmail($request->email);

        //if  the recipient does not exist, send invitation to join the team
        if ( ! $recipient ) 
        {
            $this->createInvitation(false, $team, $request->email);
            return response()->json([
                'message' => 'Invitation Sent to User'
            ], 200);
        }

        // Check if the team already has the user
        if ($team->hasUser($recipient)) 
        {
            return response()->json([
                'message' => 'This user seems to be a team member already'
            ], 422);
        }

        // Send invitation to the user
        $this->createInvitation(true, $team, $request->email);    
        
        return response()->json([
            'message' => 'Invitation Sent to User'
        ], 200);
    }

    public function resend($id)
    {
        $invitation = $this->invitations->find($id);

        $this->authorize('resend', $invitation);

        // Check if the user ows the team
        if ( ! $user->isOwnerOfTeam($invitation->team)) 
        {
            return response()->json(
                ['email' => 'You are not the team owner'],
                401
            );
        }

        $recipient = $this->users->findByEmail($invitation->recipient_email);
        
        Mail::to($invitation->recipient_email)
                ->send( new SendInvitationToJoinTeam($invitation, ! is_null($recipient)));

        return response()->json(['message' => 'Invitation resent'], 200);
    }

    public function respond(Request $request, $id)
    {
        $this->validate($request, [
            'token'  => ['required'],
            'decision' => ['required']
        ]);

        $token = $request->token;
        $decision = $request->decision; // 'acecpt' or 'deny'
        $invitation = $this->invitations->find($id);

        // >>>>>>>>>>
        $this->authorize('respond', $invitation);
        // Check if the invitation belogns to this user
        // if ($invitation->recipient_email !== auth()->user()->email ) 
        // {
        //     return response()->json(['message' => 'This is not your invitation'], 401);
        // }
        // <<<<<<<<<<<

        // check to make sure that the tokens match
        if ($invitation->token !== $token) 
        {
            return response()->json(['message' => 'Invalid Token'], 401);
        }

        // check if acccepted
        if ($decision !== 'deny' ) 
        {
            // auth()->user()->teams()->attach($invitation->team->id);
            $this->invitations->addUserToTeam($invitation->team, auth()->id());
        }

        $invitation->delete();
        
        return response()->json(['message' => 'Successful proccess respond'], 200);
    }

    public function destroy(Request $request, $id)
    {
        $invitation = $this->invitations->find($id);

        // llamado a la politica para validar:
        $this->authorize('delete', $invitation);

        $invitation->delete();

        return response()->json(['message' => 'Successful proccess deleted'], 200);
    }    


    protected function createInvitation(bool $user_exists, Team $team, string $email)
    {
        $invitation = $this->invitations->create([
            'team_id'         => $team->id,
            'sender_id'       => auth()->id(),
            'recipient_email' => $email,
            'token'           => md5(uniqid(microtime()))
        ]);

        Mail::to($email)
                ->send( new SendInvitationToJoinTeam($invitation, $user_exists));
    }

}
