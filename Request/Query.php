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
        $this->array = filter_input_array(INPUT_GET);

        $uri = $this->getUri();
        if (!preg_match('/\/$/', $uri)) {
            $uri .= '/';
        }

        // Key and values in query
        $uriData = array_values(array_filter(explode('/', parse_url($uri)['path'])));

        $name = null;
        for ($i = 0; $i < sizeof($uriData); $i++) {

            // Action
            if ($i % 2) {
                if (!empty($name)) {
                    $this->array[$name] = $uriData[$i] == 'default' ? null : $uriData[$i];
                }
            } else {

                // Controller
                $name = $uriData[$i];

                // First action for one controller
                if (!isset($uriData[$i + 1])) {
                    $this->array[$name] = 'default';
                }
            }
        }

        // Default controller and action
        if (empty($this->array)) {
            $this->array = ['main' => 'default'];
        }

        $this->array = array_reverse($this->array);
        [$this->array['_name_'], $this->array['_action_']] = [
            array_keys($this->array)[0],
            array_values($this->array)[0]
        ];
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
