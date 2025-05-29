<?php

declare(strict_types=1);

namespace Laradom\ORM\Console\Commands;

use Illuminate\Console\Command;
use Laradom\ORM\Mapping\EntityMetadataFactory;

class ClearCacheCommand extends Command
{
    protected $signature = 'laradom:clear-cache';

    protected $description = 'Clearing the entity metadata cache';

    public function __construct(
        private readonly EntityMetadataFactory $metadataFactory,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Clearing the metadata cache...');

        $this->metadataFactory->clearCache();

        $this->info('The metadata cache has been successfully cleared.');

        return self::SUCCESS;
    }
}
