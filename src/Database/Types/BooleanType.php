<?php

declare(strict_types=1);

namespace TCG\Voyager\Database\Types;

use Doctrine\DBAL\ParameterType;
use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;

/**
 * Type that maps an SQL boolean to a PHP boolean.
 */
class BooleanType extends Type
{
    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBooleanTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $platform->convertBooleansToDatabaseValue($value);
    }

    /**
     * @param T $value
     *
     * @return (T is null ? null : bool)
     *
     * @template T
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?bool
    {
        return $platform->convertFromBoolean($value);
    }

    public function getBindingType(): ParameterType
    {
        return ParameterType::BOOLEAN;
    }
}