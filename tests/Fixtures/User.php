<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    protected $connection = 'legacy';

    protected $primaryKey = 'user_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
        'site_id' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }
}
