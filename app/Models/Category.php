<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model
{
    protected $fillable = [
        'name',
        'limit',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
