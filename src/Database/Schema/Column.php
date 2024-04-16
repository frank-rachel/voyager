<?php
namespace TCG\Voyager\Database\Schema;

class Column
{
    protected $name;
    protected $type;
    protected $options = [];

    public function __construct(string $name, string $type, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
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

    // Implement other missing methods as needed...

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'options' => $this->options,
        ];
    }
}
