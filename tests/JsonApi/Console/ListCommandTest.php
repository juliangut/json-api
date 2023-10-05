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

namespace Jgut\JsonApi\Tests\Console;

use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Console\ListCommand;
use Jgut\JsonApi\Mapping\Metadata\IdentifierMetadata;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Jgut\JsonApi\Tests\Stubs\ConsoleOutputStub;
use Jgut\Mapping\Metadata\MetadataResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * @internal
 */
class ListCommandTest extends TestCase
{
    public function testNoResources(): void
    {
        $command = new ListCommand(new Configuration([
            'sources' => [],
        ]));

        $input = new ArgvInput([], $command->getDefinition());
        $input->setArgument('search', '/|#|~|%|!');

        $output = new ConsoleOutputStub();

        static::assertSame(1, $command->execute($input, $output));
        static::assertStringContainsString('No resources to show', $output->getOutput());
    }

    public function testSearchRegex(): void
    {
        $resources = $this->getMockedResources();

        $metadataResolver = $this->getMockBuilder(MetadataResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataResolver->expects(static::once())
            ->method('getMetadata')
            ->willReturn($resources);

        $command = new ListCommand(new Configuration(['metadataResolver' => $metadataResolver]));

        $input = new ArgvInput([], $command->getDefinition());
        $input->setArgument('search', '/m/i');

        $output = new ConsoleOutputStub();

        static::assertSame(0, $command->execute($input, $output));
        static::assertStringContainsString('Name', $output->getOutput());
        static::assertStringNotContainsString('Phone', $output->getOutput());
        static::assertStringNotContainsString('Address', $output->getOutput());
    }

    public function testSearchString(): void
    {
        $resources = $this->getMockedResources();

        $metadataResolver = $this->getMockBuilder(MetadataResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataResolver->expects(static::once())
            ->method('getMetadata')
            ->willReturn($resources);

        $command = new ListCommand(new Configuration(['metadataResolver' => $metadataResolver]));

        $input = new ArgvInput([], $command->getDefinition());
        $input->setArgument('search', 'd');

        $output = new ConsoleOutputStub();

        static::assertSame(0, $command->execute($input, $output));
        static::assertStringContainsString('Address', $output->getOutput());
        static::assertStringNotContainsString('Name', $output->getOutput());
        static::assertStringNotContainsString('Person ', $output->getOutput());
    }

    public function testSortClass(): void
    {
        $resources = $this->getMockedResources();

        $metadataResolver = $this->getMockBuilder(MetadataResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataResolver->expects(static::once())
            ->method('getMetadata')
            ->willReturn($resources);

        $command = new ListCommand(new Configuration(['metadataResolver' => $metadataResolver]));

        $input = new ArgvInput([], $command->getDefinition());

        $output = new ConsoleOutputStub();

        $command->execute($input, $output);

        static::assertMatchesRegularExpression('/ +Name.+\n +Address.+\n +Phone/', $output->getOutput());
    }

    public function testSortName(): void
    {
        $resources = $this->getMockedResources();

        $metadataResolver = $this->getMockBuilder(MetadataResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataResolver->expects(static::once())
            ->method('getMetadata')
            ->willReturn($resources);

        $command = new ListCommand(new Configuration(['metadataResolver' => $metadataResolver]));

        $input = new ArgvInput([], $command->getDefinition());
        $input->setOption('sort', 'name');

        $output = new ConsoleOutputStub();

        $command->execute($input, $output);

        static::assertMatchesRegularExpression('/ +Address.+\n +Name.+\n +Phone/', $output->getOutput());
    }

    public function testReverseSort(): void
    {
        $resources = $this->getMockedResources();

        $metadataResolver = $this->getMockBuilder(MetadataResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataResolver->expects(static::once())
            ->method('getMetadata')
            ->willReturn($resources);

        $command = new ListCommand(new Configuration(['metadataResolver' => $metadataResolver]));

        $input = new ArgvInput([], $command->getDefinition());
        $input->setOption('sort', 'class');
        $input->setOption('reverse', true);

        $output = new ConsoleOutputStub();

        $command->execute($input, $output);

        static::assertMatchesRegularExpression('/ +Phone.+\n +Address.+\n +Name/', $output->getOutput());
    }

    /**
     * @return list<ResourceObjectMetadata>
     */
    private function getMockedResources(): array
    {
        $resourceA = (new ResourceObjectMetadata(self::class, 'Name'))
            ->setIdentifier(new IdentifierMetadata(self::class, 'id'))
            ->setSchema('/Test/Name')
            ->setGroup('groupA');

        $resourceB = (new ResourceObjectMetadata(self::class, 'Phone'))
            ->setIdentifier(new IdentifierMetadata(self::class, 'id'))
            ->setSchema('/Test/PersonPhone')
            ->setGroup('groupB');

        $resourceC = (new ResourceObjectMetadata(self::class, 'Address'))
            ->setIdentifier(new IdentifierMetadata(self::class, 'id'))
            ->setSchema('/Test/PersonAddress')
            ->setGroup('groupC');

        return [$resourceB, $resourceA, $resourceC];
    }
}
