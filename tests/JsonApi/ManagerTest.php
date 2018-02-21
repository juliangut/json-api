<?php

/*
 * json-api (https://github.com/juliangut/json-api).
 * PSR-7 aware json-api integration.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Tests;

use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Encoding\Encoder;
use Jgut\JsonApi\Encoding\Factory;
use Jgut\JsonApi\Encoding\Http\QueryParametersParser;
use Jgut\JsonApi\Manager;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Schema\Container;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

/**
 * JSON-API manager tests.
 */
class ManagerTest extends TestCase
{
    public function testRequestManipulation()
    {
        $request = new ServerRequest();
        $queryParameters = new QueryParametersParser();
        $factory = new Factory();

        $manager = new Manager(new Configuration(), $factory);

        self::assertSame($factory, $manager->getFactory());

        $request = $manager->setRequestQueryParameters($request, $queryParameters);

        self::assertSame($queryParameters, $manager->getRequestQueryParameters($request));
    }

    public function testEncodeErrors()
    {
        $encoder = $this->getMockBuilder(Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encoder->expects(self::once())
            ->method('encodeErrors')
            ->will($this->returnValue('ENCODED'));
        /* @var Encoder $encoder */

        $container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var Container $container */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::once())
            ->method('createEncoder')
            ->will($this->returnValue($encoder));
        $factory->expects(self::once())
            ->method('createContainer')
            ->will($this->returnValue($container));
        /* @var Factory $factory */

        self::assertEquals('ENCODED', (new Manager(new Configuration(), $factory))->encodeErrors(new Error()));
    }

    public function testEncodeResources()
    {
        $request = new ServerRequest();
        $queryParameters = new QueryParametersParser();

        $encoder = $this->getMockBuilder(Encoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encoder->expects(self::once())
            ->method('withMeta');
        $encoder->expects(self::once())
            ->method('encodeData')
            ->will($this->returnValue('ENCODED'));
        /* @var Encoder $encoder */

        $container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var Container $container */

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factory->expects(self::once())
            ->method('createEncoder')
            ->will($this->returnValue($encoder));
        $factory->expects(self::once())
            ->method('createContainer')
            ->will($this->returnValue($container));
        /* @var Factory $factory */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getMetadata', 'getSources'])
            ->getMock();
        $configuration->expects(self::once())
            ->method('getMetadata')
            ->will($this->returnValue([]));
        $configuration->expects(self::once())
            ->method('getSources')
            ->will($this->returnValue([__DIR__ . '/Files/Annotation/Valid']));
        /* @var Configuration $configuration */

        $manager = new Manager($configuration, $factory);

        $request = $manager->setRequestQueryParameters($request, $queryParameters);

        self::assertEquals('ENCODED', $manager->encodeResources(new \stdClass(), $request));
    }
}
