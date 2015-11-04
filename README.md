HalClient
=========

A lightweight client for consuming and manipulating Hypertext Application
Language (HAL) resources.

[![Build Status](https://travis-ci.org/jsor/hal-client.svg?branch=master)](http://travis-ci.org/jsor/hal-client?branch=master)
[![Coverage Status](https://coveralls.io/repos/jsor/hal-client/badge.svg?branch=master&service=github)](https://coveralls.io/github/jsor/hal-client?branch=master)

* [Installation](#installation)
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

The Hal client requires a [HttpClientInterface](src/HttpClient/HttpClientInterface)
implementation which can handle [PSR-7](http://www.php-fig.org/psr/psr-7/)
requests and responses.

To use the default implementation shipped with this library, you need to install
Guzzle 6.

```bash
composer require guzzlehttp/guzzle:~6.0
```

Usage
-----

We will use [Propilex](http://propilex.herokuapp.com) as an example API
endpoint.

### Create the client

At a first step, we setup a `Client` instance.

```php
use Jsor\HalClient\Client;

$client = new Client('http://propilex.herokuapp.com');
```

We can now set additional headers (eg. an Authorization header) which are sent
with every request.

```php
$client = $client->withHeader('Authorization', 'Bearer 12345');
```

### Browse the API

To start browsing through the API, we first get the root resource.

```php
/** @var \Jsor\HalClient\Resource $rootResource */
$rootResource = $client->root();
```

We now follow the `p:documents` link.


```php
/** @var \Jsor\HalClient\Link $documentsLink */
$documentsLink = $rootResource->getFirstLink('documents');

$documentsResource = $documentsLink->get();

$totalDocuments = $documentsResource->getProperty('total');

foreach ($resource->getEmbed('documents') as $document) {
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

Copyright (c) 2015 Jan Sorgalla. 
Released under the [MIT License](https://github.com/jsor/hal-client/blob/master/LICENSE).
