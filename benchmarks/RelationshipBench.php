<?php

declare(strict_types=1);

namespace Laradom\Benchmarks;

use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Processor\PostProcessor\BidirectionalRelationshipPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\JoinColumnPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\JoinTablePostProcessor;
use Laradom\Tests\Integration\Mapping\TestEntity\Comment;
use Laradom\Tests\Integration\Mapping\TestEntity\Tag;
use Laradom\Tests\Integration\Mapping\TestEntity\User;
use PhpBench\Attributes as Bench;

class RelationshipBench
{
    private EntityMetadataFactory $metadataFactory;
    private BidirectionalRelationshipPostProcessor $bidirectionalProcessor;
    private JoinColumnPostProcessor $joinColumnProcessor;
    private JoinTablePostProcessor $joinTableProcessor;

    public function __construct()
    {
        $this->metadataFactory = BenchmarkContext::getComponent('entityMetadataFactory');
        $this->bidirectionalProcessor = BenchmarkContext::getComponent('bidirectionalProcessor');
        $this->joinColumnProcessor = BenchmarkContext::getComponent('joinColumnProcessor');
        $this->joinTableProcessor = BenchmarkContext::getComponent('joinTableProcessor');
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(100)]
    public function benchBidirectionalRelationships(): void
    {
        $allMetadata = [];

        if (class_exists(User::class)) {
            $allMetadata[] = $this->metadataFactory->getEntityMetadata(User::class);
        }

        if (class_exists(Comment::class)) {
            $allMetadata[] = $this->metadataFactory->getEntityMetadata(Comment::class);
        }

        if (empty($allMetadata)) {
            return;
        }

        $this->bidirectionalProcessor->process($allMetadata);
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(100)]
    public function benchJoinColumnProcessing(): void
    {
        $allMetadata = [];

        if (class_exists(User::class)) {
            $allMetadata[] = $this->metadataFactory->getEntityMetadata(User::class);
        }

        if (class_exists(Comment::class)) {
            $allMetadata[] = $this->metadataFactory->getEntityMetadata(Comment::class);
        }

        if (empty($allMetadata)) {
            return;
        }

        $this->joinColumnProcessor->process($allMetadata);
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(100)]
    public function benchJoinTableProcessing(): void
    {
        $allMetadata = [];

        if (class_exists(Tag::class)) {
            $allMetadata[] = $this->metadataFactory->getEntityMetadata(Tag::class);
        }

        if (empty($allMetadata)) {
            return;
        }

        $this->joinTableProcessor->process($allMetadata);
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(100)]
    public function benchFullRelationshipProcessing(): void
    {
        $allMetadata = [];

        // Проверяем наличие классов перед загрузкой метаданных
        if (class_exists(User::class)) {
            $allMetadata[] = $this->metadataFactory->getEntityMetadata(User::class);
        }

        if (class_exists(Comment::class)) {
            $allMetadata[] = $this->metadataFactory->getEntityMetadata(Comment::class);
        }

        if (class_exists(Tag::class)) {
            $allMetadata[] = $this->metadataFactory->getEntityMetadata(Tag::class);
        }

        if (empty($allMetadata)) {
            return;
        }

        $this->bidirectionalProcessor->process($allMetadata);
        $this->joinColumnProcessor->process($allMetadata);
        $this->joinTableProcessor->process($allMetadata);
    }
}
