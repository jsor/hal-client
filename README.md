HalClient
=========

A lightweight PHP client for consuming and manipulating
[Hypertext Application Language (HAL)](https://tools.ietf.org/html/draft-kelly-json-hal)
resources.

[![Build Status](https://travis-ci.org/jsor/hal-client.svg?branch=master)](http://travis-ci.org/jsor/hal-client?branch=master)
[![Coverage Status](https://coveralls.io/repos/jsor/hal-client/badge.svg?branch=master&service=github)](https://coveralls.io/github/jsor/hal-client?branch=master)

* [Installation](#installation)
* [Usage](#usage)
* [License](#license)

Installation
------------

Install the latest version with [Composer](http://getcomposer.org).

```bash
composer require jsor/hal-client
```

Check the [Packagist page](https://packagist.org/packages/jsor/hal-client) for
all available versions.

### HTTP Client dependency

The Hal client requires a [HttpClientInterface](src/HttpClient/HttpClientInterface.php)
implementation which can handle [PSR-7](http://www.php-fig.org/psr/psr-7/)
requests and responses.

To use the default implementations shipped with this library, you need to
install Guzzle 6 or Guzzle 5.

```bash
composer require guzzlehttp/guzzle:"^5.0||^6.0"
```

### URI template handling

In order to expand URI templates in HAL links, you must either provide a global
`\uri_template` function (e.g. by installing the
[uri_template](https://github.com/ioseb/uri-template) extension) or install the
`guzzlehttp/guzzle` package (version `^5.0` or `^6.0`).

Usage
-----

We will use [Propilex](http://propilex.herokuapp.com) as an example API
endpoint.

### Create the client

At a first step, we setup a `HalClient` instance.

```php
use Jsor\HalClient\HalClient;

$client = new HalClient('http://propilex.herokuapp.com');
```

We can now set additional headers (eg. an Authorization header) which are sent
with every request.

```php
$client = $client->withHeader('Authorization', 'Bearer 12345');
```

Note, that a client instance is [immutable](https://en.wikipedia.org/wiki/Immutable_object),
which means, any call to change the state of the instance returns a **new**
instance leaving the original instance unchanged.

```php
// Wrong!
$client->withHeader('Authorization', '...');
$resource = $client->get('/protected');

// Correct!
$client = $client->withHeader('Authorization', '...');
$resource = $client->get('/protected');
```

### Browse the API

To start browsing through the API, we first get the root resource.

```php
/** @var \Jsor\HalClient\HalResource $rootResource */
$rootResource = $client->root();
```

We now follow the `p:documents` link.


```php
/** @var \Jsor\HalClient\HalLink $documentsLink */
$documentsLink = $rootResource->getFirstLink('documents');

$documentsResource = $documentsLink->get();

$totalDocuments = $documentsResource->getProperty('total');

foreach ($resource->getResource('documents') as $document) {
    echo $document->getProperty('title') . PHP_EOL;
}
```

If there is a second page with more documents, we can follow the `next` link.

```php
if ($documentsResource->hasLink('next')) {
    $nextDocumentsResource = $documentsResource->getFirstLink('next')->get();
}
```

Ok, let's create a new document.

```php
$newDocument = $documentsResource->post([
    'body' => [
        'title' => 'Sampl document',
        'body'  => 'Lorem ipsum'
    ]
]);
```

Oh noes! A typo in the document title. Let's fix it.

```php
$changedDocument = $newDocument->put([
    'body' => [
        'title' => 'Sampe document',
        'body'  => $newDocument->getProperty('body')
    ]
]);
```

Damn, we give up.

```php
$changedDocument->delete();
```

License
-------

Copyright (c) 2015-2020 Jan Sorgalla.
Released under the [MIT License](https://github.com/jsor/hal-client/blob/master/LICENSE).
