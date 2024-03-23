<?php declare(strict_types=1);

namespace Digua\Request;

use Digua\Interfaces\Request\FilteredInput as FilteredInputInterface;

class FilteredInput implements FilteredInputInterface
{
    const INPUT_POST = INPUT_POST;

    const INPUT_GET = INPUT_GET;

    const INPUT_COOKIE = INPUT_COOKIE;

    const INPUT_SERVER = INPUT_SERVER;

    const INPUT_FILES = 7;

    /**
     * @var array
     */
    private static array $sanitize = [
        self::INPUT_POST   => FILTER_SANITIZE_SPECIAL_CHARS,
        self::INPUT_GET    => FILTER_SANITIZE_SPECIAL_CHARS,
        self::INPUT_COOKIE => FILTER_SANITIZE_SPECIAL_CHARS,
        self::INPUT_SERVER => FILTER_DEFAULT,
        self::INPUT_FILES  => null
    ];

    /**
     * @var array
     */
    private static array $input = [
        self::INPUT_POST   => null,
        self::INPUT_GET    => null,
        self::INPUT_COOKIE => null,
        self::INPUT_SERVER => null,
        self::INPUT_FILES  => null
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
        if ($type == self::INPUT_SERVER) {
            return (array)filter_var_array($_SERVER, self::$sanitize[$type]);
        }

        if ($type == self::INPUT_FILES) {
            return array_filter(
                $this->collectFilesList(),
                self::$sanitize[$type]['callback'] ?? null,
                ARRAY_FILTER_USE_BOTH
            );
        }

        // JSON POST requests
        if ($type == self::INPUT_POST && isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json') {
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

    /**
     * @return array
     */
    protected function collectFilesList(): array
    {
        $index = 0;
        $list  = [];

        foreach ($_FILES as $field => $info) {
            if (!is_array($info['name'])) {
                $list[$index] = ['field' => $field, 'count' => 1, 'index' => 0, ...$info];
                $index++;
                continue;
            }

            // Numbered list only, no string keys of in $_FILES (ex files[] not files[filed])
            if (!array_is_list($info['name'])) {
                continue;
            }

            $count = sizeof($info['name']);
            for ($i = 0; $i < $count; $i++) {
                $list[$index] = ['field' => $field, 'count' => $count, 'index' => $i];
                foreach ($info as $key => $value) {
                    $list[$index][$key] = $value[$i];
                }
                $index++;
            }
        }

        return $list;
    }
}