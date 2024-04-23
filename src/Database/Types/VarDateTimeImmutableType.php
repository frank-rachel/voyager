<?php

declare(strict_types=1);

namespace TCG\Voyager\Database\Types;

use DateTimeImmutable;
use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Exception;

/**
 * Immutable type of {@see VarDateTimeType}.
 */
class VarDateTimeImmutableType extends DateTimeImmutableType
{
    /**
     * @param T $value
     *
     * @return (T is null ? null : string)
     *
     * @template T
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof DateTimeImmutable) {
            return $value->format($platform->getDateTimeFormatString());
        }

        throw InvalidType::new(
            $value,
            static::class,
            ['null', DateTimeImmutable::class],
        );
    }

    /**
     * @param T $value
     *
     * @return (T is null ? null : DateTimeImmutable)
     *
     * @template T
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DateTimeImmutable
    {
        if ($value === null || $value instanceof DateTimeImmutable) {
            return $value;
        }

        try {
            $dateTime = new DateTimeImmutable($value);
        } catch (Exception $e) {
            throw ValueNotConvertible::new($value, DateTimeImmutable::class, $e->getMessage(), $e);
        }

        return $dateTime;
    }
}
