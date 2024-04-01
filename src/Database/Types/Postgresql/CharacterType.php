<?php

namespace FrankRachel\Voyager\Database\Types\Postgresql;

use FrankRachel\Voyager\Database\Types\Common\CharType;

class CharacterType extends CharType
{
    public const NAME = 'character';
    public const DBTYPE = 'bpchar';
}
