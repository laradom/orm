<?php

declare(strict_types=1);

namespace Laradom\ORM\Console\Commands;

use Illuminate\Console\Command;
use Laradom\ORM\Mapping\EntityMetadataFactory;

class ScanEntitiesCommand extends Command
{
    protected $signature = 'laradom:scan-entities';

    protected $description = 'Scanning entities, resetting and warming up the metadata cache';

    public function __construct(
        private readonly EntityMetadataFactory $metadataFactory,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Entity scanning...');

        $this->info('Clearing the metadata cache...');
        $this->metadataFactory->clearCache();

        $this->info('Warming up the metadata cache...');
        $metadataMap = $this->metadataFactory->getAllMetadata();

        if (empty($metadataMap)) {
            $this->warn('The entities were not found.');

            return self::FAILURE;
        }

        $this->info(sprintf('%d entities found. The metadata cache has been successfully warmed up.', count($metadataMap)));

        return self::SUCCESS;
    }
}
