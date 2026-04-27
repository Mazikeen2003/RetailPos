<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action', 'user', 'user_id', 'details', 'level'
    ];
    
    /**
     * @property int $id
     * @property string $action
     * @property string $user
     * @property int|null $user_id
     * @property string $details
     * @property string $level
     * @mixin \Illuminate\Database\Eloquent\Model
     */
    protected $casts = [
        'user_id' => 'integer',
    ];
}
