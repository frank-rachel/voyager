<?php

namespace TCG\Voyager\Database\Types\Postgresql;

use TCG\Voyager\Database\Types\Common\VarCharType;

class CharacterVaryingType extends VarCharType
{
    // public const NAME = 'character varying';
    public const NAME = 'string';
    public const DBTYPE = 'varchar';
    protected $category = 'String';
	
}
