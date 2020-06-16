<?php
    namespace App\Repositories\Contracts;
    use Illuminate\Http\Request;
    
    interface IInvitation {
        public function addUserToTeam($team, $user_id);
        public function removeUserFromTeam($team, $user_id);
    }
