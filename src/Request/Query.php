<?php declare(strict_types=1);

namespace Digua\Request;

use Digua\Traits\Data as DataTrait;
use Digua\Interfaces\Request\{
    FilteredCollection as FilteredCollectionInterface,
    FilteredInput as FilteredInputInterface,
    Query as RequestQueryInterface
};

class Query implements FilteredCollectionInterface, RequestQueryInterface
{
    use DataTrait;

    /**
     * @var string
     */
    private string $path = '/';

    /**
     * @var array
     */
    private array $paths = [];

    /**
     * @var array
     */
    private array $pathAsList = [];

    /**
     * @var array
     */
    private array $query = [];

    /**
     * @var array
     */
    private array $exported = [];

    /**
     * @param FilteredInputInterface $filteredInput
     */
    public function __construct(private readonly FilteredInputInterface $filteredInput = new FilteredInput)
    {
        $this->buildPathFromUri();
        $this->shake();
    }

    /**
     * @inheritdoc
     */
    public function shake(): void
    {
        $this->collectPathFromUri();
        $this->collectQueryFromUri();

        $this->array = $this->filteredInput->filteredList(INPUT_GET)
            + $this->pathAsList
            + $this->query
            + $this->exported;
    }

    /**
     * @inheritdoc
     */
    public function filtered(): FilteredInputInterface
    {
        return $this->filteredInput;
    }

    /**
     * @return void
     */
    protected function buildPathFromUri(): void
    {
        $this->path = parse_url($this->getUri(), PHP_URL_PATH) ?? '/';
    }

    /**
     * Convert path to array.
     *
     * @return void
     * @example
     *         /key/value to [key => value]
     *         /key/s-value to [key => sValue]
     *         /s-key/s-value to [sKey => sValue]
     */
    protected function collectPathFromUri(): void
    {
        if (!empty($this->path) && $this->path !== '/') {
            $paths = array_filter(
                explode('/', trim($this->path, '/')),
                fn($value) => !!preg_match('/\w/', trim($value))
            );

            // converting every odd key-value to keyValue
            $this->paths = array_map(
                fn($value, $key) => !!($key % 2)
                    ? $value
                    : str_replace('-', '', lcfirst(ucwords($value, '-'))),
                $paths,
                array_keys($paths)
            );

            if (!!(sizeof($this->paths) % 2)) {
                $this->paths[] = null;
            }

            $this->pathAsList = array_column(array_chunk($this->paths, 2), 1, 0);
        }
    }

    /**
     * @return void
     */
    protected function collectQueryFromUri(): void
    {
        $query = parse_url($this->getUri(), PHP_URL_QUERY);
        if (!empty($query)) {
            parse_str($query, $result);
            $this->query = filter_var_array($result, FILTER_SANITIZE_SPECIAL_CHARS);
        }
    }

    /**
     * @inheritdoc
     */
    public function getUri(): string
    {
        return filter_var($this->filteredInput->filteredVar(INPUT_SERVER, 'REQUEST_URI') ?? '/', FILTER_SANITIZE_URL);
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public function getPathAsList(): array
    {
        return $this->pathAsList;
    }

    /**
     * @inheritdoc
     */
    public function getHost(): string
    {
        $scheme = $this->filteredInput->filteredVar(INPUT_SERVER, 'REQUEST_SCHEME') ?? 'http';
        $host   = $this->filteredInput->filteredVar(INPUT_SERVER, 'HTTP_HOST') ?? '';
        return strtolower($scheme . '://' . $host);
    }

    /**
     * @inheritdoc
     */
    public function getLocation(): string
    {
        return $this->getHost() . $this->getUri();
    }

    /**
     * @inheritdoc
     */
    public function isAsync(): bool
    {
        return $this->filteredInput->filteredVar(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    /**
     * @inheritdoc
     */
    public function getFromPath(int|string ...$variables): ?array
    {
        if (!empty($this->paths)) {
            $found = [];
            foreach ($variables as $item) {
                // Search by index
                if (is_int($item) && isset($this->paths[$item - 1])) {
                    $found[] = $this->paths[$item - 1];
                    continue;
                }

                // Search by name
                if (isset($this->pathAsList[$item])) {
                    $found[$item] = $this->pathAsList[$item];
                }
            }

            return $found ?: null;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function exportFromPath(int|string ...$variables): static
    {
        $variables = $this->getFromPath(...$variables);
        if (!empty($variables)) {
            foreach ($variables as $name => $value) {
                if (is_int($name)) {
                    $this->exported['_' . $value . '_'] = $name;
                } else {
                    $this->exported['_' . $name . '_'] = $value;
                }

                // Removing a variable from path
                $path = (!is_int($name) ? '/' . $name : '') . '/' . $value;
                if (($pos = strpos($this->path, $path)) !== false) {
                    $this->path = substr_replace($this->path, '', $pos, strlen($path));
                }
            }

            $this->shake();
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function buildPath(string ...$path): static
    {
        $path = array_map(fn($value) => trim($value, '/'), array_filter($path));
        $this->path = filter_var('/' . implode('/', $path), FILTER_SANITIZE_URL);
        return $this;
    }
}
