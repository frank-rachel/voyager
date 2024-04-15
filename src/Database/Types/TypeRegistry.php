<?php
namespace App\Database\Types;

class TypeRegistry
{
    private static $types = [];

    public static function addType($name, Type $type)
    {
        self::$types[$name] = $type;
    }

    public static function getType($name)
    {
        return self::$types[$name] ?? null;
    }
}
