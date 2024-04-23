<?php

namespace TCG\Voyager\Database\Types\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class TxidSnapshotType extends Type
{
    public const NAME = 'txid_snapshot';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'txid_snapshot';
    }
}
