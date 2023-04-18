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
     * @var string[]
     */
    private array $defExport = ['page'];

    /**
     * @param FilteredInputInterface $filteredInput
     */
    public function __construct(private readonly FilteredInputInterface $filteredInput = new FilteredInput)
    {
        $this->shake();
    }

    /**
     * @inheritdoc
     */
    public function shake(): void
    {
        $this->array = $this->filteredInput->filteredList(INPUT_GET);
        $this->collectQueryFromUri();
        $this->buildPathFromUri();
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
        $urlData = parse_url($this->getUri());
        if (isset($urlData['path']) && !empty($urlData['path'])) {
            $this->path = $urlData['path'];
            $this->exportFromPath(...$this->defExport);
        }
    }

    /**
     * @return void
     */
    protected function collectQueryFromUri(): void
    {
        $urlData = parse_url($this->getUri());
        if (isset($urlData['query']) && !empty($urlData['query'])) {
            parse_str($urlData['query'], $result);
            $this->array += filter_var_array($result, FILTER_SANITIZE_SPECIAL_CHARS);
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
        if ($this->path !== '/') {
            $path = $this->path;
            if (!preg_match('/\/$/', $path)) {
                $path .= '/';
            }

            $uriData = filter_var_array(
                array_values(array_filter(explode('/', $path))),
                FILTER_SANITIZE_SPECIAL_CHARS
            );

            $found = [];
            foreach ($variables as $item) {
                for ($i = 0; $i < sizeof($uriData); $i++) {
                    // Search by index
                    if (($i + 1) == $item) {
                        $found[] = $uriData[$i];
                        break;
                    }

                    // Search by name
                    if ($item == $uriData[$i]) {
                        $found[$item] = $uriData[$i + 1] ?? null;
                        break;
                    }
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
                $this->set((string)$name, $value);

                // Removing a variable from path
                $path = (!is_int($name) ? '/' . $name : '') . '/' . $value;
                if (($pos = strpos($this->path, $path)) !== false) {
                    $this->path = substr_replace($this->path, '', $pos, strlen($path));
                }
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function buildPath(string ...$path): static
    {
        $path       = array_map(fn($value) => trim($value, '/'), array_filter($path));
        $this->path = filter_var('/' . implode('/', $path), FILTER_SANITIZE_URL);
        return $this;
    }
}
