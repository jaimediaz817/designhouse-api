<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
// use Illuminate\Auth\Notifications\VerifyEmail;
use App\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    // TODO: add: SpatialTrait
    use Notifiable, SpatialTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

     // TODO: add
    protected $fillable = [
        'name', 'email', 'password',
        'tagline',
        'about',
        'username',
        'location',
        'available_to_hire',
        'formatted_address'
    ];

    // TODO: add
    protected $spatialFields = [
        'location',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected $appends = [
        'photo_url'
    ];

    public function getPhotoUrlAttribute()
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . 'jpg?s=200&d=mm';
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // TODO:add - Método para enviar email de notificación
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
    // todo:add:end


    // TODO: relations
    public function designs()
    {
        return $this->hasMany(Design::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }    

    // Teams that the user belongs to
    public function teams()
    {
        return $this->belongsToMany(Team::class)
                    ->withTimestamps();
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'recipient_email', 'email');
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'participants');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getChatWithUser($user_id)
    {   
        $chat = $this->chats()
                    ->whereHas('participants', function( $query ) use ($user_id ) {
                        $query->where('user_id', $user_id);
                    })
                    ->first();
        return $chat;
    }
    // TODO:relations:end





    // Equipos propios 
    public function ownedTeams()
    {
        return $this->teams()
                    ->where('owner_id', $this->id);
    }

    // Es dueño del equipo?
    public function isOwnerOfTeam($team)
    {
        return $this->teams()
                ->where('id', $team->id)
                ->where('owner_id', $this->id)
                ->count();
    }        


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }    
}
