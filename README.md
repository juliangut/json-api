[![PHP version](https://img.shields.io/badge/PHP-%3E%3D8.0-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/v/juliangut/json-api.svg?style=flat-square)](https://packagist.org/packages/juliangut/json-api)
[![License](https://img.shields.io/github/license/juliangut/json-api.svg?style=flat-square)](https://github.com/juliangut/json-api/blob/master/LICENSE)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/json-api.svg?style=flat-square)](https://packagist.org/packages/juliangut/json-api/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/json-api.svg?style=flat-square)](https://packagist.org/packages/juliangut/json-api/stats)

# json-api

Easy JSON:API integration.

## Installation

### Composer

```
composer require juliangut/json-api
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
use Neomerx\JsonApi\Schema\Error;

$configuration = new Configuration([
    'sources' => ['/path/to/resource/files'],
]);

$jsonApiManager = new Manager($configuration);

// Get encoded errors
$jsonApiManager->encodeErrors(new Error());

// Get encoded resources
$jsonApiManager->encodeResources(new MyClass(), new ServerRequestInstance());
```

### Configuration

* `sources` must be an array containing arrays of configurations to create MappingDriver objects:
    * `type` one of \Jgut\JsonApi\Mapping\Driver\DriverFactory constants: `DRIVER_ATTRIBUTE`, `DRIVER_PHP`, `DRIVER_JSON`, `DRIVER_XML`, `DRIVER_YAML` or `DRIVER_ANNOTATION` **if no driver, defaults to DRIVER_ATTRIBUTE**
    * `path` a string path or array of paths to where mapping files are located (files or directories) **REQUIRED if no driver**
    * `driver` an already created \Jgut\JsonApi\Mapping\Driver\DriverInterface object **REQUIRED if no type AND path**
* `attributeName` name of the PSR-7 Request attribute that will hold query parameters for resource encoding, defaults to 'JSON_API_query_parameters'
* `schema` class name implementing \Jgut\JsonApi\Schema\MetadataSchemaInterface (\Jgut\JsonApi\Schema\MetadataSchema by default)
* `prefix` prefix for generated URLs
* `metadataResolver` an instance of \Jgut\Mapping\Metadata\MetadataResolver. It is highly recommended to provide a PSR-16 cache to metadata resolver on production
* `encodingOptions` global encoding options, an instance of \Jgut\JsonApi\Encoding\OptionsInterface
* `jsonApiVersion` none by default
* `jsonApiMeta` optional global metadata

## Middleware

Use PSR-15 middleware `Jgut\JsonApi\Middleware\JsonApiMiddleware` in order to validate request being a valid JSON:API specification request

```php
use Jgut\JsonApi\Manager;
use Jgut\JsonApi\Middleware\JsonApiMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;

/** @var ResponseFactoryInterface $responseFactory */
/** @var Manager $jsonApiManager */

$middleware = new JsonApiMiddleware($responseFactory, $jsonApiManager);

// Add the middleware to any PSR-15 compatible library/framework, such as Slim, Mezzio, etc 
```

## Console command

```php
use Symfony\Component\Console\Application;
use Jgut\Slim\PHPDI\Command\ListCommand;

/** @var \Slim\App $app */
$container = $app->getContainer();

$cli = new Application('Slim CLI');
$cli->add(new ListCommand($container));

$app->run();
```

### List container definitions

List defined container definitions supporting searching

## Resource mapping

Resources can be defined in two basic ways: by writing them down in definition files of various types or directly defined in attributes on classes

### Attributes

#### ResourceObject (Class level)

Identifies each JSON:API resource. Its presence is mandatory in each resource class

Accepts an optional "name" that overrides default (class name with lowercase first letter)

```php
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;
use Jgut\JsonApi\Mapping\Attribute\ResourcePrefix;
use Jgut\JsonApi\Mapping\Attribute\ResourceSchema;

#[ResourceObject(
    name: 'company',
    prefix: 'resourcePrefix',
    schema: 'customSchemaClass',
    meta: ['meta1' => 'value'],
)]
class Company
{
}
```

* `name`, optional, resource name, lowercase first letter class name by default
* `prefix`, optional, resource url prefix when links are included
* `schema`, optional, class name implementing \Jgut\JsonApi\Schema\MetadataSchemaInterface. Override default one

#### Identifier (Property level)

The resource identifier

```php
use Jgut\JsonApi\Mapping\Attribute\Getter;
use Jgut\JsonApi\Mapping\Attribute\Identifier;
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;
use Jgut\JsonApi\Mapping\Attribute\Setter;

#[ResourceObject]
class Owner
{
    #[Identifier(
        name: 'identifier',
        getter: 'getIdentifier',
        setter: 'setIdentifier',
        meta: ['meta1' => 'value'],
    )]
    protected string $id;
}
```

* `name`, optional, identifier name, lowercase first letter property name by default
* `getter`, optional, method in the class that gives access to the property. By default, uppercase first letter property name prefixed by "is" for booleans or "get" for the rest of types
* `setter`, optional, method in the class that sets the value for the property, uppercase first letter property name prefixed by "set"
* `meta`, optional, list of optional array/value array of identifier metadata

#### Attribute (Property level)

A resource attribute

```php
use Jgut\JsonApi\Mapping\Attribute\Attribute;
use Jgut\JsonApi\Mapping\Attribute\Getter;
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;
use Jgut\JsonApi\Mapping\Attribute\Setter;

#[ResourceObject]
class Company
{
    #[Attribute(
        name: 'title',
        getter: 'getTitle',
        setter: 'setTitle',
        groups: ['view'],
    )]
    protected string $title;
}
```

* `name`, optional, attribute name, lowercase first letter property name by default
* `getter`, optional, method in the class that gives access to the property. By default, uppercase first letter property name prefixed by "is" for booleans or "get" for the rest of types
* `setter`, optional, method in the class that sets the value for the property, uppercase first letter property name prefixed by "set"
* `groups`, optional, array of groups to which the attribute belongs

#### Relationship (Property level)

A resource relationship

```php
use Jgut\JsonApi\Mapping\Attribute\Relationship;
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;

#[ResourceObject]
class Company
{
    #[Relationship(
        name: 'owner',
        getter: 'getOwner',
        setter: 'setOwner',
        groups: ['view'],
    )]
    protected Owner $companyOwner;
}
```

* `name`, optional, relationship name, lowercase first letter property name by default
* `getter`, optional, method in the class that gives access to the property. By default, uppercase first letter property name prefixed by "is" for booleans or "get" for the rest of types
* `setter`, optional, method in the class that sets the value for the property, uppercase first letter property name prefixed by "set"
* `groups`, optional, array of groups to which the relationship belongs

#### Links

```php
use Jgut\JsonApi\Mapping\Attribute\Link;
use Jgut\JsonApi\Mapping\Attribute\LinkRelated;
use Jgut\JsonApi\Mapping\Attribute\LinkSelf;
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;
use Jgut\JsonApi\Mapping\Attribute\Relationship;

#[ResourceObject]
#[LinkSelf(false)]
#[LinkRelated]
#[Link(
    href: 'http://...',
    title: 'example',
    meta: ['meta1' => 'value'],
)]
class Company
{
    #[Relationship]
    #[LinkSelf]
    #[LinkRelated(false)]
    #[Link(
        href: 'http://...',
        title: 'example',
        meta: ['meta1' => 'value'],
    )]
    protected Owner $companyOwner;
}
```

##### LinkSelf (Class level)

Determines whether self link is included in the response

##### LinkRelated (Class level)

Determines whether self link is included in the response when the resource is included as a relationship

##### Link (Class and Property level)

Adds as many custom link to the resource or field as needed

* `href`, required, href of the link
* `title`, optional, link title
* `meta`, optional, list of optional link metadata (see metadata section below)

#### Metadata

```php
use Jgut\JsonApi\Mapping\Attribute\Attribute;
use Jgut\JsonApi\Mapping\Attribute\Meta;
use Jgut\JsonApi\Mapping\Attribute\Relationship;
use Jgut\JsonApi\Mapping\Attribute\ResourceObject;

#[ResourceObject]
#[Meta(key: 'meta1', value: 'value')]
class Company
{
    #[Identifier]
    #[Meta(key: 'meta2', value: 'value')]
    protected string $id;
    
    #[Relationship]
    #[Meta(key: 'meta3', value: 'value')]
    #[Meta(key: 'meta4', value: 'value')]
    protected Owner $owner;
}
```

There are two kinds of metadata:

##### Meta Attribute (Class and Property level)

Assign one or more metadata to a resource, identifier or relationship

* `key`, required, metadata key
* `value`, required, metadata value

##### Other metadata

Link attributes accept metadata as a key/value array

### Definition files

##### PHP

```php
return [
    [
        'class' => 'CompanyClass',
        'name' => 'company',
        'prefix' => 'company',
        'schema' => 'MetadataCompanySchemaClass',
        'linkSelf' => true,
        'linkRelated' => false,
        'meta' => [
            'meta1' => 'value',
        ],
        'identifier' => [
            'property' => 'uuid',
            'name' => 'id',
            'getter' => 'getUuid',
            'setter' => 'setUuid',
            'meta' => [
                'meta2' => 'value',
            ]
        ],
        'attributes' => [
            [
                'property' => 'email',
                'name' => 'email',
                'getter' => 'getEmail',
                'setter' => 'setEmail',
            ],
        ],
        'relationships' => [
            [
                'class' => 'OwnerClass',
                'property' => 'owner',
                'name' => 'owner',
                'linkSelf' => true,
                'linkRelated' => true,
                'links' => [
                    'example' => [
                        'href' => 'http://example.com',
                        'meta' => [
                            'meta3' => 'value',
                        ],
                    ],
                ],
                'meta' => [
                    'meta4' => 'value',
                ],
            ],
        ],
    ],
];
```

##### JSON

```json
[
  {
    "class": "CompanyClass",
    "name": "company",
    "prefix": "company",
    "schema": "MetadataCompanySchemaClass",
    "linkSelf": true,
    "linkRelated": false,
    "meta": {
      "meta1": "value"
    },
    "identifier": {
      "property": "uuid",
      "name": "id",
      "getter": "getUuid",
      "setter": "setUuid",
      "meta": {
        "meta2": "value"
      }
    },
    "attributes": [
      {
        "property": "email",
        "name": "email",
        "getter": "getEmail",
        "setter": "setEmail"
      }
    ],
    "relationships": [
      {
        "class": "OwnerClass",
        "property": "owner",
        "name": "owner",
        "linkSelf": true,
        "linkRelated": true,
        "links": {
          "example": {
            "href": "http://example.com",
            "meta": {
              "meta3": "value"
            }
          }
        },
        "meta": {
          "meta4": "value"
        }
      }
    ]
  }
]
```

##### XML

```xml
<?xml version="1.0" encoding="utf-8"?>
<root>
  <resource
    class="CompanyClass"
    name="company"
    prefix="company"
    schema="MetadataCompanySchemaClass"
    linkSelf="true"
    linkRelated="false"
  >
    <meta>
      <meta1>value</meta1>
    </meta>
    <identifier property="uuid" name="id" getter="getUuid" setter="setUuid">
      <meta>
        <meta2>value</meta2>
      </meta>
    </identifier>
    <attributes>
      <attribute1 property="email" name="email" getter="getEmail" setter="setEmail"/>
    </attributes>
    <relationships>
      <relationship1 class="OwnerClass" property="owner" name="owner" linkSelf="true" linkRelated="true">
        <links>
          <example href="http://example.com">
            <meta>
              <meta3>value</meta3>
            </meta>
          </example>
        </links>
        <meta>
          <meta4>value</meta4>
        </meta>
      </relationship1>
    </relationships>
  </resource>
</root>
```

##### YAML

```yaml
- class: "CompanyClass"
  name: "company"
  prefix: "company"
  schema: "MetadataCompanySchemaClass"
  linkSelf: true
  linkRelated: false
  meta:
    meta1: "value"
  identifier:
    property": "uuid"
    name: "id"
    getter: "getUuid"
    setter: "setUuid"
    meta:
      meta2: "value"
  attributes:
    - property: "email"
      name: "email"
      getter: "getEmail"
      setter: "setEmail"
  relationships:
    - class: "OwnerClass"
      property: "owner"
      name: "owner"
      linkSelf: true
      linkRelated: true
      links:
        example:
          href: "http://example.com"
          meta:
            meta3: "value"
      meta:
        meta4: "value"
```

### Annotations

__Annotations are deprecated and will be removed eventually. Use Attribute mapping when possible__.

You need to require Doctrine's annotation package

```
composer require doctrine/annotations
```

#### ResourceObject (Class level)

Identifies each resource. Its presence is mandatory on each resource class

```php
use Jgut\JsonApi\Mapping\Annotation as JJM;

/**
 * @JJM\ResourceObject(
 *     name="company",
 *     schema="CustomSchemaClass",
 *     prefix="resourcePrefix",
 *     linkSelf=true,
 *     linkRelated=false,
 *     links={"link1": "http://...", "link2": "http://..."},
 *     meta={"meta1" => "value", "meta2" => "value"}
 * )
 */
class Company
{
}
```

* `name`, optional, resource name, lowercase first letter class name by default
* `prefix`, optional, resource url prefix when links are included
* `schema`, optional, class name implementing \Jgut\JsonApi\Schema\MetadataSchemaInterface. Override default one
* `linkSelf`, optional bool, display self link, null by default
* `linkRelated`, optional bool, display self link when included, null by default
* `links`, optional, list of optional key/value array of resource links
* `meta`, optional, list of optional array/value array of resource metadata

#### Identifier (Property level)

The resource identifier

```php
use Jgut\JsonApi\Mapping\Annotation as JJM;

/**
 * @JJM\ResourceObject
 */
class Owner
{
    /**
     * @JJM\Identifier(
     *     name="id",
     *     getter="getId",
     *     setter="setId",
     *     meta={"meta1" => "value", "meta2" => "value"}
     * )
     */
    protected $id;
}
```

* `name`, optional, identifier name, lowercase first letter property name by default
* `getter`, optional, method in the class that gives access to the property. By default, uppercase first letter property name prefixed by "is" for booleans or "get" for the rest of types
* `setter`, optional, method in the class that sets the value for the property, uppercase first letter property name prefixed by "set"
* `meta`, optional, list of optional array/value array of identifier metadata

#### Attribute (Property level)

Defines each and every attribute accessible on the resource

```php
use Jgut\JsonApi\Mapping\Annotation as JJM;

/**
 * @JJM\ResourceObject
 */
class Company
{
    /**
     * @JJM\Attribute(
     *     name="email",
     *     getter="getEmail",
     *     setter="setEmail",
     *     groups={"view"}
     * )
     */
    protected string $email;
}
```

* `name`, optional, attribute name, lowercase first letter property name by default
* `getter`, optional, method in the class that gives access to the property. By default, uppercase first letter property name prefixed by "is" for booleans or "get" for the rest of types
* `setter`, optional, method in the class that sets the value for the property, uppercase first letter property name prefixed by "set"
* `groups`, optional, array of groups to which the attribute belongs

#### Relationship (Property level)

Identifies this resource relationships

```php
use Jgut\JsonApi\Mapping\Annotation as JJM;

/**
 * @JJM\ResourceObject
 */
class Company
{
    /**
     * @JJM\Relationship(
     *     name="company",
     *     getter="getCompany",
     *     setter="setCompany",
     *     groups=["view"]
     *     linkSelf=true,
     *     linkRelated=false,
     *     links={"link1": "http://...", "link2": "http://..."],
     *     meta={"meta1" => ·"value", "meta2" => "value"}
     * )
     */
    protected Owner $company;
}
```

* `name`, optional, relationship name, lowercase first letter property name by default
* `getter`, optional, method in the class that gives access to the property. By default, uppercase first letter property name prefixed by "is" for booleans or "get" for the rest of types
* `setter`, optional, method in the class that sets the value for the property, uppercase first letter property name prefixed by "set"
* `groups`, optional, array of groups to which the relationship belongs
* `linkSelf`, optional bool, display self link, null by default
* `linkRelated`, optional bool, display self link when included, null by default
* `links`, optional, list of optional key/value array of resource links
* `meta`, optional, list of optional array/value array of relationship metadata

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/json-api/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/json-api/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/json-api/blob/master/LICENSE) included with the source code for a copy of the license terms.
