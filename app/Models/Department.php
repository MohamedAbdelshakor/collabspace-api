<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    /**
     * @return HasMany<ProjectMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'department_id');
    }
}
