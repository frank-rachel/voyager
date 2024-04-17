<?php
namespace TCG\Voyager\Database\Schema;

class Column
{
    public $name;
    public $type;
    public $options;
    public $nullable;
    public $default;
    public $length;
    public $precision;
    public $scale;

    public function __construct($name, $type, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        $this->nullable = $options['nullable'] ?? true;
        $this->default = $options['default'] ?? null;
        $this->length = $options['length'] ?? null;
        $this->precision = $options['precision'] ?? null;
        $this->scale = $options['scale'] ?? null;
        $this->options['unsigned'] = $options['unsigned'] ?? false;
        $this->options['fixed'] = $options['fixed'] ?? false;
        $this->options['notnull'] = $options['notnull'] ?? true;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'nullable' => $this->nullable,
            'default' => $this->default,
            'length' => $this->length,
            'precision' => $this->precision,
            'scale' => $this->scale,
            'unsigned' => $this->getUnsigned(),
            'fixed' => $this->getFixed(),
            'notnull' => $this->getNotnull()
        ];
    }

    // Additional getter and setter methods
    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
        $this->nullable = $options['nullable'] ?? $this->nullable;
        $this->default = $options['default'] ?? $this->default;
        $this->length = $options['length'] ?? $this->length;
        $this->precision = $options['precision'] ?? $this->precision;
        $this->scale = $options['scale'] ?? $this->scale;
        $this->options['unsigned'] = $options['unsigned'] ?? $this->options['unsigned'];
        $this->options['fixed'] = $options['fixed'] ?? $this->options['fixed'];
        $this->options['notnull'] = $options['notnull'] ?? $this->options['notnull'];
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): void
    {
        $this->length = $length;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function setPrecision(?int $precision): void
    {
        $this->precision = $precision;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function setScale(?int $scale): void
    {
        $this->scale = $scale;
    }

    public function getUnsigned(): bool
    {
        return $this->options['unsigned'];
    }

    public function setUnsigned(bool $unsigned): void
    {
        $this->options['unsigned'] = $unsigned;
    }

    public function getFixed(): bool
    {
        return $this->options['fixed'];
    }

    public function setFixed(bool $fixed): void
    {
        $this->options['fixed'] = $fixed;
    }

    public function getNotnull(): bool
    {
        return $this->options['notnull'];
    }

    public function setNotnull(bool $notnull): void
    {
        $this->options['notnull'] = $notnull;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault($default): void
    {
        $this->default = $default;
    }
}
