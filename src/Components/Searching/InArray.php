<?php declare(strict_types=1);

namespace Digua\Components\Searching;

class InArray
{
    /**
     * Data.
     *
     * @var array
     */
    private array $array;

    /**
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }

    /**
     * Find key in array.
     *
     * @param string $key
     * @param mixed  $value
     * @return array
     */
    public function find(string $key, mixed $value): array
    {
        $elements = $this->array;
        if (empty($value)) {
            return $elements;
        }

        foreach ($elements as $index => $element) {
            if (!isset($element[$key]) || stripos($element[$key], $value) === false) {
                unset($elements[$index]);
            }
        }

        return $elements;
    }
}
