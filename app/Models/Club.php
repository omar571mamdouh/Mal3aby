<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $table = 'clubs';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'logo',
    ];
    
   public function branches()
{
    return $this->hasMany(Branch::class);
}
}