<?php declare(strict_types=1);

namespace Digua\Interfaces\Request;

interface FilteredInput
{
    /**
     * @param int       $type
     * @param array|int $options
     * @return void
     */
    public static function setSanitize(int $type, array|int $options): void;

    /**
     * @param int $type
     * @return array|int|null
     */
    public static function getSanitize(int $type): array|int|null;

    /**
     * @param int $type
     * @return static
     */
    public function refresh(int $type): static;

    /**
     * @param int $type
     * @return array
     */
    public function filteredList(int $type): array;

    /**
     * @param int        $type
     * @param string|int $name
     * @return mixed
     */
    public function filteredVar(int $type, string|int $name): mixed;
}