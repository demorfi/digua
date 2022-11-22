<?php

namespace Digua\Enums;

enum ContentType: string
{
    /**
     * Content type json.
     */
    case JSON = 'application/json';

    /**
     * Content type html.
     */
    case HTML = 'text/html';

    /**
     * Content type text.
     */
    case TEXT = 'text';
}
