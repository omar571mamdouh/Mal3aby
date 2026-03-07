<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = ['branch_id','name','type','image','description','active'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function courts()
    {
        return $this->hasMany(Court::class);
    }
}
