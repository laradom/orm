<?php

declare(strict_types=1);

namespace Laradom\Benchmarks;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\Tests\Integration\Mapping\TestEntity\Comment;
use Laradom\Tests\Integration\Mapping\TestEntity\Tag;
use Laradom\Tests\Integration\Mapping\TestEntity\User;
use PhpBench\Attributes as Bench;

class MetadataBench
{
    private EntityMetadataFactory $metadataFactory;
    private CacheRepository $cache;

    public function __construct()
    {
        $this->metadataFactory = BenchmarkContext::getComponent('entityMetadataFactory');
        $this->cache = BenchmarkContext::getComponent('cache');
    }

    #[Bench\BeforeMethods(['clearCache'])]
    public function benchMetadataWithoutCache(): void
    {
        $this->setMetadataCacheEnabled(false);
        $this->loadAllMetadata();
    }

    #[Bench\BeforeMethods(['clearCache'])]
    public function benchMetadataWithCacheColdStart(): void
    {
        $this->setMetadataCacheEnabled(true);
        $this->loadAllMetadata();
    }

    #[Bench\BeforeMethods(['prepareCache'])]
    public function benchMetadataWithCacheHotStart(): void
    {
        $this->setMetadataCacheEnabled(true);
        $this->loadAllMetadata();
    }

    #[Bench\BeforeMethods(['clearCache'])]
    public function benchSingleEntityMetadataWithoutCache(): void
    {
        $this->setMetadataCacheEnabled(false);
        $this->metadataFactory->getEntityMetadata(User::class);
    }

    #[Bench\BeforeMethods(['prepareCache'])]
    public function benchSingleEntityMetadataWithCache(): void
    {
        $this->setMetadataCacheEnabled(true);
        $this->metadataFactory->getEntityMetadata(User::class);
    }

    public function clearCache(): void
    {
        $this->cache->flush();
    }

    public function prepareCache(): void
    {
        $this->clearCache();
        $this->setMetadataCacheEnabled(true);
        $this->loadAllMetadata();
    }

    private function loadAllMetadata(): void
    {
        $this->metadataFactory->getEntityMetadata(User::class);

        if (class_exists(Comment::class)) {
            $this->metadataFactory->getEntityMetadata(Comment::class);
        }

        if (class_exists(Tag::class)) {
            $this->metadataFactory->getEntityMetadata(Tag::class);
        }
    }

    private function setMetadataCacheEnabled(bool $enabled): void
    {
        if (!$enabled) {
            $this->cache->flush();
        }
    }
}
