<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class MediumBlobType extends Type
{
    public const NAME = 'mediumblob';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'mediumblob';
    }
}
