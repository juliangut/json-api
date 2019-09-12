[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.0-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/juliangut/json-api.svg?style=flat-square)](https://packagist.org/packages/juliangut/json-api)
[![License](https://img.shields.io/github/license/juliangut/json-api.svg?style=flat-square)](https://github.com/juliangut/json-api/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/juliangut/json-api.svg?style=flat-square)](https://travis-ci.org/juliangut/json-api)
[![Style Check](https://styleci.io/repos/122273176/shield)](https://styleci.io/repos/122273176)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/json-api.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/json-api)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/json-api.svg?style=flat-square)](https://coveralls.io/github/juliangut/json-api)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/json-api.svg?style=flat-square)](https://packagist.org/packages/juliangut/json-api/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/json-api.svg?style=flat-square)](https://packagist.org/packages/juliangut/json-api/stats)

# json-api

PSR-7 aware json-api integration.

## Installation

### Composer

```
composer require juliangut/json-api
```

doctrine/annotations to parse annotations

```php
composer require doctrine/annotations
```

symfony/yaml to parse yaml files

```
composer require symfony/yaml
```

## Usage

Require composer autoload file

```php
require './vendor/autoload.php';
```

```php
use Jgut\JsonApi\Manager;
use Jgut\JsonApi\Configuration;
use Neomerx\JsonApi\Document\Error;

$configuration = new Configuration([
    'sources' => ['/path/to/resource/files'],
]);

$jsonApiManager = new Manager($configuration);

// Get encoded errors
$jsonApiManager->encodeErrors([new Error()]);

// Get encoded resources
$jsonApiManager->encodeResources(new MyClass(), new RequestInstance());
```

### Configuration

* `sources` must be an array containing arrays of configurations to create MappingDriver objects:
    * `type` one of \Jgut\JsonApi\Mapping\Driver\DriverFactory constants: `DRIVER_ANNOTATION`, `DRIVER_PHP`, `DRIVER_JSON`, `DRIVER_XML` or `DRIVER_YAML` **defaults to DRIVER_ANNOTATION if no driver**
    * `path` a string path or array of paths to where mapping files are located (files or directories) **REQUIRED if no driver**
    * `driver` an already created \Jgut\JsonApi\Mapping\Driver\DriverInterface object **REQUIRED if no type AND path**
* `attributeName` name of the PSR-7 Request attribute that will hold query parameters for resource encoding
* `schemaClass` class name implementing \Jgut\JsonApi\Schema\MetadataSchemaInterface (\Jgut\JsonApi\Schema\MetadataSchema by default)
* `urlPrefix` prefix for generated URLs, defaults to 'JSON_API_query_parameters'
* `metadataResolver` an instance of \Jgut\Mapping\Metadata\MetadataResolver. It is highly recommended to provide a PSR-16 cache to metadata resolver on production
* `encoderOptions` an instance of \Neomerx\JsonApi\Encoder\EncoderOptions

### Resources

Resources can be defined in two basic ways: by setting them in definition files of various types or directly defined in annotations on classes

#### Annotations

#### Definition files

###### PHP

###### JSON

###### XML

###### YAML

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/json-api/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/json-api/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/json-api/blob/master/LICENSE) included with the source code for a copy of the license terms.
