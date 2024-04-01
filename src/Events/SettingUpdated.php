<?php

namespace FrankRachel\Voyager\Events;

use Illuminate\Queue\SerializesModels;
use FrankRachel\Voyager\Models\Setting;

class SettingUpdated
{
    use SerializesModels;

    public $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }
}
