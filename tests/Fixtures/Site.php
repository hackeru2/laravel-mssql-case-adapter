<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Site extends Model
{
    protected $connection = 'legacy';

    protected $primaryKey = 'site_id';

    public $timestamps = false;

    protected $guarded = [];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'site_id', 'site_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'site_id', 'site_id');
    }
}
