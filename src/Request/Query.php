<?php declare(strict_types=1);

namespace Digua\Request;

use Digua\Interfaces\{
    NamedCollection as NamedCollectionInterface,
    Request\Query as RequestQueryInterface
};
use Digua\Traits\Data as DataTrait;

class Query implements RequestQueryInterface, NamedCollectionInterface
{
    use DataTrait;

    /**
     * @var string
     */
    private string $path = '/';

    /**
     * @var string[]
     */
    private array $extract = ['page'];

    public function __construct()
    {
        $this->array = (array)filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);
        $parseUrl    = parse_url($this->getUri());

        if (!empty($parseUrl)) {
            // Add query values
            if (isset($parseUrl['query']) && !empty($parseUrl['query'])) {
                parse_str($parseUrl['query'], $result);
                $this->array += filter_var_array($result, FILTER_SANITIZE_SPECIAL_CHARS);
            }

            // Check path transfer
            if (isset($parseUrl['path']) && !empty($parseUrl['path'])) {
                $this->path = $parseUrl['path'];
                $this->exportFromPath(...$this->extract);
            }
        }
    }

    /**
     * @inheritdoc
     * @internal Not use INPUT_SERVER as not always available with cli.
     */
    public function getUri(): string
    {
        return filter_var($_SERVER['REQUEST_URI'] ?? '/', FILTER_SANITIZE_URL);
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
     * @internal Not use INPUT_SERVER as not always available with cli.
     */
    public static function getHost(): string
    {
        return strtolower(($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''));
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function getLocation(): string
    {
        return $this->getHost() . $this->getUri();
    }

    /**
     * @inheritdoc
     * @internal Not use INPUT_SERVER as not always available with cli.
     */
    public function isAsync(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * @inheritdoc
     */
    public function getFromPath(int|string ...$variables): array|null
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
                $this->set($name, $value);

                // Removing a variable from path
                $path = (!is_int($name) ? '/' . $name : '') . '/' . $value;
                if (($pos = strpos($this->path, $path)) !== false) {
                    $this->path = substr_replace($this->path, '', $pos, strlen($path));
                }
            }
        }

        return $this;
    }
}
