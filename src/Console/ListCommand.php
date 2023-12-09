<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Console;

use Jgut\JsonApi\Configuration;
use Jgut\JsonApi\Mapping\Metadata\ResourceObjectMetadata;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @phpstan-import-type Source from Configuration
 */
class ListCommand extends SymfonyCommand
{
    public const NAME = 'jsonapi:resource:list';
    private const SORT_CLASS = 'class';
    private const SORT_NAME = 'name';

    protected static $defaultName = self::NAME;

    public function __construct(
        private Configuration $configuration,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('List JSON:API resources')
            ->addOption('sort', null, InputOption::VALUE_OPTIONAL, 'Route sorting: name, class', 'class')
            ->addOption('reverse', null, InputOption::VALUE_NONE, 'Reverse sorting')
            ->addArgument('search', InputArgument::OPTIONAL, 'Resource search pattern')
            ->setHelp(<<<'HELP'
            The <info>%command.name%</info> command lists registered resources.

            You can search for resources by a pattern:

              <info>%command.full_name%</info> <comment>/todo/</comment>

            Results can be sorted by "name" or "class":

              <info>%command.full_name%</info> <comment>--sort=name</comment>

            Results order can be reversed:

              <info>%command.full_name%</info> <comment>--reverse</comment>

            HELP);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $ioStyle = new SymfonyStyle($input, $output);

        $resources = $this->getResources($input);
        if (\count($resources) === 0) {
            $ioStyle->error('No resources to show');

            return self::FAILURE;
        }

        /** @var string $sorting */
        $sorting = $input->getOption('sort');
        if (!\in_array($sorting, [self::SORT_CLASS, self::SORT_NAME], true)) {
            $ioStyle->error(sprintf('Unsupported sorting type "%s"', $sorting));

            return self::FAILURE;
        }

        $sortCallback = $sorting === self::SORT_NAME
            ? static fn(ResourceObjectMetadata $metadataA, ResourceObjectMetadata $metadataB): int
                => $metadataA->getName() <=> $metadataB->getName()
            : static fn(ResourceObjectMetadata $metadataA, ResourceObjectMetadata $metadataB): int
                => $metadataA->getSchema() <=> $metadataB->getSchema();

        usort($resources, $sortCallback);

        if ($input->getOption('reverse') !== false) {
            $resources = array_reverse($resources);
        }

        $ioStyle->comment('List of defined resources');

        (new Table($output))
            ->setStyle('symfony-style-guide')
            ->setHeaders(['name', 'Class', 'Identifier', 'Group'])
            ->setRows($this->getTableRows($resources))
            ->render();

        $ioStyle->newLine();

        return self::SUCCESS;
    }

    /**
     * @return list<ResourceObjectMetadata>
     */
    private function getResources(InputInterface $input): array
    {
        $sources = $this->configuration->getSources();
        /** @var list<ResourceObjectMetadata> $resources */
        $resources = $this->configuration->getMetadataResolver()
            ->getMetadata($sources);

        $searchPattern = $this->getSearchPattern($input);
        if ($searchPattern === null) {
            return $resources;
        }

        return array_values(array_filter(
            $resources,
            static function (ResourceObjectMetadata $metadata) use ($searchPattern): bool {
                return preg_match($searchPattern, $metadata->getName()) === 1
                    || (
                        $metadata->getSchema() !== null
                        && preg_match($searchPattern, $metadata->getSchema()) === 1
                    );
            },
        ));
    }

    private function getSearchPattern(InputInterface $input): ?string
    {
        /** @var string|null $searchPattern */
        $searchPattern = $input->getArgument('search');
        if ($searchPattern === null) {
            return $searchPattern;
        }

        foreach (['~', '!', '\/', '#', '%', '\|'] as $delimiter) {
            $pattern = sprintf('/^%1$s(.*)%1$s[imsxeuADSUXJ]*$/', $delimiter);
            if (preg_match($pattern, $searchPattern) === 1) {
                return $searchPattern;
            }
        }

        return sprintf('/%s/i', preg_quote($searchPattern, '/'));
    }

    /**
     * Get resources formatted for table.
     *
     * @param array<ResourceObjectMetadata> $resources
     *
     * @return list<list<string|null>>
     */
    private function getTableRows(array $resources): array
    {
        return array_values(array_map(
            static function (ResourceObjectMetadata $resource): array {
                return [
                    $resource->getName(),
                    $resource->getSchema(),
                    $resource->getIdentifier()
                        ->getName(),
                    $resource->getGroup(),
                ];
            },
            $resources,
        ));
    }
}
