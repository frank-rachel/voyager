<?php

namespace FrankRachel\Voyager\Database\Types\Postgresql;

use FrankRachel\Voyager\Database\Types\Common\VarCharType;

class CharacterVaryingType extends VarCharType
{
    public const NAME = 'character varying';
    public const DBTYPE = 'varchar';
}
