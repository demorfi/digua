<?php declare(strict_types=1);

namespace Digua\Request;

use Digua\Interfaces\Request\FilteredInput as FilteredInputInterface;

class FilteredInput implements FilteredInputInterface
{
    /**
     * @var array
     */
    private static array $sanitize = [
        INPUT_POST   => FILTER_SANITIZE_SPECIAL_CHARS,
        INPUT_GET    => FILTER_SANITIZE_SPECIAL_CHARS,
        INPUT_COOKIE => FILTER_SANITIZE_SPECIAL_CHARS,
        INPUT_SERVER => FILTER_DEFAULT
    ];

    /**
     * @var array
     */
    private static array $input = [
        INPUT_POST   => null,
        INPUT_GET    => null,
        INPUT_COOKIE => null,
        INPUT_SERVER => null
    ];

    /**
     * @inheritdoc
     */
    public static function setSanitize(int $type, array|int $options): void
    {
        self::$sanitize[$type] = $options;
    }

    /**
     * @param int $type
     * @return int|array|null
     */
    public static function getSanitize(int $type): array|int|null
    {
        return self::$sanitize[$type] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function refresh(int $type): static
    {
        self::$input[$type] = null;
        return $this;
    }

    /**
     * @param int $type
     * @return array
     */
    protected function filterInput(int $type): array
    {
        // Not use filter_input_array(INPUT_SERVER) as not always available with cli.
        if ($type == INPUT_SERVER) {
            return (array)filter_var_array($_SERVER, self::$sanitize[$type]);
        }

        // JSON POST requests
        if ($type == INPUT_POST && isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json') {
            $json = (array)json_decode(file_get_contents('php://input'), true);
            return (array)filter_var_array($json, self::$sanitize[$type]);
        }

        return (array)filter_input_array($type, self::$sanitize[$type]);
    }

    /**
     * @inheritdoc
     */
    public function filteredList(int $type): array
    {
        return self::$input[$type] ?? (self::$input[$type] = $this->filterInput($type));
    }

    /**
     * @inheritdoc
     */
    public function filteredVar(int $type, string|int $name): mixed
    {
        return $this->filteredList($type)[$name] ?? null;
    }
}