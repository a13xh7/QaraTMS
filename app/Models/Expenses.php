<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'category_id',
        'amount',
        'description',
        'created_at'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
