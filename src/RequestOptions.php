<?php

namespace Jsor\HalClient;

/**
 * @author Michael Dowling
 */
final class RequestOptions
{
    /**
     * body: (array|string|null|callable|iterator|object) Body to send in the
     * request. If the the value is an array, it will be serialized and a
     * Content-Type: application/x-www-form-urlencoded header will be set (but
     * only if none is already present).
     */
    const BODY = 'body';

    /**
     * headers: (array) Associative array of HTTP headers. Each value MUST be
     * a string or array of strings.
     */
    const HEADERS = 'headers';

    /**
     * query: (array|string) Associative array of query string values to add
     * to the request. This option uses PHP's http_build_query() to create
     * the string representation. Pass a string value if you need more
     * control than what this method provides
     */
    const QUERY = 'query';

    /**
     * version: (float) Specifies the HTTP protocol version to attempt to use.
     */
    const VERSION = 'version';
}
