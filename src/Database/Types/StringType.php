<?php

declare(strict_types=1);

namespace TCG\Voyager\Database\Types;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;

/**
 * Type that maps an SQL VARCHAR to a PHP string.
 */
class StringType extends Type
{
    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }
}
