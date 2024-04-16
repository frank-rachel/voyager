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
        $this->nullable = $options['nullable'] ?? false;
        $this->default = $options['default'] ?? null;
        $this->length = $options['length'] ?? null;
        $this->precision = $options['precision'] ?? null;
        $this->scale = $options['scale'] ?? null;
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
        ];
    }


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
    }

    public function getLength(): ?int
    {
        return $this->options['length'] ?? null;
    }

    public function setLength(int $length): void
    {
        $this->options['length'] = $length;
    }

    public function getPrecision(): ?int
    {
        return $this->options['precision'] ?? null;
    }

    public function setPrecision(int $precision): void
    {
        $this->options['precision'] = $precision;
    }

    public function getScale(): int
    {
        return $this->options['scale'] ?? 0;
    }

    public function setScale(int $scale): void
    {
        $this->options['scale'] = $scale;
    }

    public function getUnsigned(): bool
    {
        return $this->options['unsigned'] ?? false;
    }

    public function setUnsigned(bool $unsigned): void
    {
        $this->options['unsigned'] = $unsigned;
    }

    public function getFixed(): bool
    {
        return $this->options['fixed'] ?? false;
    }

    public function setFixed(bool $fixed): void
    {
        $this->options['fixed'] = $fixed;
    }

    public function getNotnull(): bool
    {
        return $this->options['notnull'] ?? true;
    }

    public function setNotnull(bool $notnull): void
    {
        $this->options['notnull'] = $notnull;
    }

    public function getDefault(): mixed
    {
        return $this->options['default'] ?? null;
    }

    public function setDefault(mixed $default): void
    {
        $this->options['default'] = $default;
    }


}
