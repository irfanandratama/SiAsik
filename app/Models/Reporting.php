<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Reporting extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'informer',
        'informer_name',
        'room_id',
        'assign_to',
        'condition_id',
        'description',
        'status_id',
        'created_by',
        'updated_by',
        'approval_message',
    ];

    public function informer_i(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'informer');
    }

    public function assign(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'assign_to');
    }

    public function cleaner(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'assign_to');
    }

    public function room(): HasOne
    {
        return $this->hasOne(Room::class, 'id', 'room_id');
    }

    public function condition(): HasOne
    {
        return $this->hasOne(Condition::class, 'id', 'condition_id');
    }

    public function status(): HasOne
    {
        return $this->hasOne(Status::class, 'id', 'status_id');
    }

    public function creator(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function updater(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }

}
