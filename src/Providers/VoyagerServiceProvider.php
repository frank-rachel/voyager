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
        TypeRegistry::addType('integer', new IntegerType());
        TypeRegistry::addType('text', new TextType());
        // Add more types as needed
    }
}
