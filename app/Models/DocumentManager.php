<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentManager extends Model
{
    use HasFactory;

    protected $table = 'documents_manager';

    protected $fillable = [
        'title',
        'content',
        'category',
        'tags',
        'author_id',
        'last_edited_by_id',
        'state',
        'parent_id',
        'position',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'last_edited_by_id');
    }

    public function reviewers()
    {
        return $this->belongsToMany(User::class, 'document_manager_reviewer', 'document_manager_id', 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(DocumentManager::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentManager::class, 'parent_id')->orderBy('position');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    // Scope to get root documents (no parent)
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('position');
    }
}
