<?php
    namespace App\Repositories\Contracts;
    use Illuminate\Http\Request;
    
    interface IChat {
        public function createParticipants($chatId, array $data);
        public function getUserChats();
    }
