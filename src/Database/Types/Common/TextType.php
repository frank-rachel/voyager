<?php

namespace FrankRachel\Voyager\Database\Types\Common;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class TextType extends Type
{
    public const NAME = 'text';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'text';
    }
}
