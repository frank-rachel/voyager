<?php

namespace FrankRachel\Voyager\Events;

use Illuminate\Queue\SerializesModels;
use FrankRachel\Voyager\Database\Schema\Table;

class TableAdded
{
    use SerializesModels;

    public $table;

    public function __construct(Table $table)
    {
        $this->table = $table;

        event(new TableChanged($table->name, 'Added'));
    }
}
