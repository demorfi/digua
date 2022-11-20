<?php

namespace Digua\Request;

use Digua\Abstracts\Data;

class Query extends Data
{
    /**
     * Query constructor.
     */
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
                if (!preg_match('/\/$/', $parseUrl['path'])) {
                    $parseUrl['path'] .= '/';
                }

                // Key and values in query
                $uriData = filter_var_array(
                    array_values(array_filter(explode('/', $parseUrl['path']))),
                    FILTER_SANITIZE_SPECIAL_CHARS
                );

                $name = null;
                for ($i = 0; $i < sizeof($uriData); $i++) {

                    // Controller name
                    if (!isset($this->array['_name_'])) {
                        $this->array['_name_'] = $uriData[$i];
                        continue;
                    }

                    // Action name
                    if (!isset($this->array['_action_'])) {
                        $this->array['_action_'] = $uriData[$i];
                        continue;
                    }

                    // Var value
                    if ($i % 2) {
                        if (!empty($name)) {
                            $this->array[$name] = $uriData[$i] == 'default' ? null : $uriData[$i];
                        }
                    } else {

                        // Var name
                        $name = $uriData[$i];
                    }
                }
            }
        }

        $this->array['_name_']   ??= 'main';
        $this->array['_action_'] ??= 'default';
    }

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->array['_name_'];
    }

    /**
     * Get controller action.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->array['_action_'];
    }

    /**
     * Get route.
     *
     * @return string
     */
    public function getRoute(): string
    {
        return strtolower($this->array['_name_'] . '.' . $this->array['_action_']);
    }

    /**
     * Has route.
     *
     * @param string $path Route path
     * @return bool
     */
    public function hasRoute(string $path): bool
    {
        return (bool)preg_match('/^' . preg_quote($path, '/') . '/', $this->getRoute());
    }

    /**
     * Get URI path.
     *
     * @internal Not use INPUT_SERVER as not always available with cli.
     * @return string
     */
    public function getUri(): string
    {
        return filter_var($_SERVER['REQUEST_URI'] ?? '/', FILTER_SANITIZE_URL);
    }

    /**
     * Get host path.
     *
     * @internal Not use INPUT_SERVER as not always available with cli.
     * @return string
     */
    public static function getHost(): string
    {
        return strtolower(($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST']);
    }

    /**
     * Get location path.
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->getHost() . $this->getUri();
    }

    /**
     * Is request ajax.
     *
     * @internal Not use INPUT_SERVER as not always available with cli.
     * @return bool
     */
    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}
