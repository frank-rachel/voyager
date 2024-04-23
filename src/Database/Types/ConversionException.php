<?php

declare(strict_types=1);

namespace TCG\Voyager\Database\Types;

use Doctrine\DBAL\Exception;

/**
 * Conversion Exception is thrown when the database to PHP conversion fails.
 *
 * @psalm-immutable
 */
class ConversionException extends \Exception implements Exception
{
}
