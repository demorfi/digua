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
        $this->array = (array)filter_input_array(INPUT_GET);
        $parseUrl    = parse_url($this->getUri());

        if (!empty($parseUrl)) {

            // Add query values
            if (isset($parseUrl['query']) && !empty($parseUrl['query'])) {
                parse_str($parseUrl['query'], $result);
                $this->array += $result;
            }

            // Check path transfer
            if (isset($parseUrl['path']) && !empty($parseUrl['path'])) {
                if (!preg_match('/\/$/', $parseUrl['path'])) {
                    $parseUrl['path'] .= '/';
                }

                // Key and values in query
                $uriData = array_values(array_filter(explode('/', $parseUrl['path'])));
                $name    = null;

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
        return ($this->array['_name_']);
    }

    /**
     * Get controller action.
     *
     * @return string
     */
    public function getAction(): string
    {
        return ($this->array['_action_']);
    }

    /**
     * Get route.
     *
     * @return string
     */
    public function getRoute(): string
    {
        return (strtolower($this->array['_name_'] . '.' . $this->array['_action_']));
    }

    /**
     * Has route.
     *
     * @param string $path Route path
     * @return bool
     */
    public function hasRoute(string $path): bool
    {
        return ((bool)preg_match('/^' . preg_quote($path, '/') . '/', $this->getRoute()));
    }

    /**
     * Get URI path.
     *
     * @return string
     */
    public function getUri(): string
    {
        [$uri] = array_values(filter_input_array(INPUT_SERVER, ['REQUEST_URI' => FILTER_SANITIZE_URL], true));
        return ($uri);
    }

    /**
     * Get host path.
     *
     * @return string
     */
    public static function getHost(): string
    {
        $data = (array)filter_input_array(INPUT_SERVER);
        return (htmlspecialchars($data['REQUEST_SCHEME']) . '://' . htmlspecialchars($data['HTTP_HOST']));
    }

    /**
     * Get location path.
     *
     * @return string
     */
    public function getLocation(): string
    {
        return ($this->getHost() . $this->getUri());
    }

    /**
     * Is request ajax.
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return (filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest');
    }
}
