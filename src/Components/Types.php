<?php declare(strict_types=1);

namespace Digua\Components;

class Types
{
    const ALIAS_TYPES = ['boolean' => 'bool', 'integer' => 'int'];

    /**
     * @var string
     */
    protected readonly string $short;

    /**
     * @var string
     */
    protected readonly string $long;

    /**
     * @param mixed $value
     */
    public function __construct(protected readonly mixed $value)
    {
        $type = strtolower(gettype($this->value));

        $this->long  = $type === 'double' ? 'float' : $type;
        $this->short = self::ALIAS_TYPES[$this->long] ?? $this->long;
    }

    /**
     * @param mixed $value
     * @return static
     */
    public static function value(mixed $value): static
    {
        return new static($value);
    }

    /**
     * @param string $type
     * @return static
     */
    public static function type(string $type): static
    {
        settype($type, $type);
        return new static($type);
    }

    /**
     * @param string $type
     * @return static
     */
    public function to(string $type): static
    {
        $value = $this->value;
        if (($type == 'bool' || $type == 'boolean') && ($value === 'true' || $value === 'false')) {
            $value = $value === 'true';
        } else {
            settype($value, $type);
        }
        return new static($value);
    }

    /**
     * @return string
     */
    public function getNameShort(): string
    {
        return $this->short;
    }

    /**
     * @return string
     */
    public function getNameLong(): string
    {
        return $this->long;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function is(string $type): bool
    {
        return $this->short === $type || $this->long === $type || $this->value instanceof $type;
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->short === 'null';
    }
}