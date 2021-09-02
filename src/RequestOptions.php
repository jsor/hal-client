<?php

namespace Jsor\HalClient;

/**
 * @author Michael Dowling
 */
final class RequestOptions
{
    /**
     * body: (array|string|null|callable|iterator|object) Body to send in the
     * request.
     *
     * If the the value is an array, it will be encoded as JSON and a
     * Content-Type: application/json header will be set (but only if no
     * Content-Type header already present).
     */
    public const BODY = 'body';

    /**
     * headers: (array) Associative array of HTTP headers. Each value MUST be
     * a string or array of strings.
     */
    public const HEADERS = 'headers';

    /**
     * query: (array|string) Associative array of query string values to add
     * to the request. This option uses PHP's http_build_query() to create
     * the string representation. Pass a string value if you need more
     * control than what this method provides.
     */
    public const QUERY = 'query';

    /**
     * return_raw_response: If set to true, instructs the client to return the
     * raw PSR-7 response object instead of a Resource object.
     */
    public const RETURN_RAW_RESPONSE = 'return_raw_response';

    /**
     * version: (float) Specifies the HTTP protocol version to attempt to use.
     */
    public const VERSION = 'version';
}
