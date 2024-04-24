<?php

declare(strict_types=1);

namespace TCG\Voyager\Database\Types;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;

/**
 * Represents a GUID/UUID datatype (both are actually synonyms) in the database.
 */
class GuidType extends StringType
{
    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }
}