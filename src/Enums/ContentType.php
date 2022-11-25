<?php declare(strict_types = 1);

namespace Digua\Enums;

enum ContentType: string
{
    case JSON = 'application/json';

    case HTML = 'text/html';

    case TEXT = 'text';
}
