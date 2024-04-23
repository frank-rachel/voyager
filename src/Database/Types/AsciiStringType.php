<?php

declare(strict_types=1);

namespace TCG\Voyager\Database\Types;

use Doctrine\DBAL\ParameterType;
use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;

final class AsciiStringType extends StringType
{
    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getAsciiStringTypeDeclarationSQL($column);
    }

    public function getBindingType(): ParameterType
    {
        return ParameterType::ASCII;
    }
}
