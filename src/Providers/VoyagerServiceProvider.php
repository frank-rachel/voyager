<?php
namespace Vendor\Package\Providers;

use Illuminate\Support\ServiceProvider;
use Vendor\Package\Database\Types\TypeRegistry;
use Vendor\Package\Database\Types\IntegerType;
use Vendor\Package\Database\Types\TextType;

class VoyagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerTypes();
    }

    private function registerTypes()
    {
        Type::addType('integer', new IntegerType());
        Type::addType('text', new TextType());
        // Add more types as needed
    }
}
