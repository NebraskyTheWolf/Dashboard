<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLogs extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type'
    ];
}
