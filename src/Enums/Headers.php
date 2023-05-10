<?php declare(strict_types=1);

namespace Digua\Enums;

enum Headers: int
{
    case CONTINUE = 100;

    case SWITCHING_PROTOCOLS = 101;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc2518
     */
    case PROCESSING = 102;

    case OK = 200;

    case CREATED = 201;

    case ACCEPTED = 202;

    case NON_AUTHORITATIVE_INFORMATION = 203;

    case NO_CONTENT = 204;

    case RESET_CONTENT = 205;

    case PARTIAL_CONTENT = 206;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc4918
     */
    case HTTP_MULTI_STATUS = 207;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc5842
     */
    case ALREADY_REPORTED = 208;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc3229
     */
    case IM_USED = 226;

    case MULTIPLE_CHOICES = 300;

    case MOVED_PERMANENTLY = 301;

    case FOUND = 302;

    case SEE_OTHER = 303;

    case NOT_MODIFIED = 304;

    case USE_PROXY = 305;

    case RESERVED = 306;

    case TEMPORARY_REDIRECT = 307;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7238
     */
    case PERMANENTLY_REDIRECT = 308;

    case BAD_REQUEST = 400;

    case UNAUTHORIZED = 401;

    case PAYMENT_REQUIRED = 402;

    case FORBIDDEN = 403;

    case NOT_FOUND = 404;

    case METHOD_NOT_ALLOWED = 405;

    case NOT_ACCEPTABLE = 406;

    case PROXY_AUTHENTICATION_REQUIRED = 407;

    case REQUEST_TIMEOUT = 408;

    case CONFLICT = 409;

    case GONE = 410;

    case LENGTH_REQUIRED = 411;

    case RECONDITION_FAILED = 412;

    case REQUEST_ENTITY_TOO_LARGE = 413;

    case REQUEST_URI_TOO_LONG = 414;

    case UNSUPPORTED_MEDIA_TYPE = 415;

    case REQUESTED_RANGE_NOT_SATISFIABLE = 416;

    case EXPECTATION_FAILED = 417;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc2324
     */
    case I_AM_A_TEAPOT = 418;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7540
     */
    case MISDIRECTED_REQUEST = 421;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc4918
     */
    case UNPROCESSABLE_ENTITY = 422;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc4918
     */
    case LOCKED = 423;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc4918
     */
    case FAILED_DEPENDENCY = 424;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc2817
     */
    case RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc2817
     */
    case UPGRADE_REQUIRED = 426;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc6585
     */
    case PRECONDITION_REQUIRED = 428;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc6585
     */
    case TOO_MANY_REQUESTS = 429;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc6585
     */
    case REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7725
     */
    case UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    case INTERNAL_SERVER_ERROR = 500;

    case NOT_IMPLEMENTED = 501;

    case BAD_GATEWAY = 502;

    case SERVICE_UNAVAILABLE = 503;

    case GATEWAY_TIMEOUT = 504;

    case VERSION_NOT_SUPPORTED = 505;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc2295
     */
    case VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc4918
     */
    case INSUFFICIENT_STORAGE = 507;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc5842
     */
    case LOOP_DETECTED = 508;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc2774
     */
    case NOT_EXTENDED = 510;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc6585
     */
    case NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * @return string
     */
    public function getText(): string
    {
        return match ($this->value) {
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Reserved for WebDAV advanced collections expired proposal',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required'
        };
    }
}
