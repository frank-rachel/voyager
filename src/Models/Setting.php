<?php

namespace FrankRachel\Voyager\Models;

use Illuminate\Database\Eloquent\Model;
use FrankRachel\Voyager\Events\SettingUpdated;

class Setting extends Model
{
    protected $table = 'settings';

    protected $guarded = [];

    public $timestamps = false;

    protected $dispatchesEvents = [
        'updating' => SettingUpdated::class,
    ];
}
