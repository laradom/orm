<?php

declare(strict_types=1);

namespace Laradom\Benchmarks;

use Laradom\ORM\Util\Inflector\EnglishInflectorStrategy;
use Laradom\ORM\Util\Naming\NamingStrategyInterface;
use PhpBench\Attributes as Bench;

class NamingStrategyBench
{
    private NamingStrategyInterface $namingStrategy;
    private EnglishInflectorStrategy $inflector;

    public function __construct()
    {
        $this->namingStrategy = BenchmarkContext::getComponent('namingStrategy');
        $this->inflector = BenchmarkContext::getComponent('inflector');
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(100)]
    public function benchClassToTableName(): void
    {
        $classNames = [
            'App\Entity\User',
            'App\Entity\UserProfile',
            'App\Entity\ArticleComment',
            'App\Entity\ProductCategory',
            'App\Entity\OrderItem',
        ];

        foreach ($classNames as $className) {
            $this->namingStrategy->classToTableName($className);
        }
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(100)]
    public function benchPropertyToColumnName(): void
    {
        $propertyNames = [
            'firstName',
            'lastName',
            'emailAddress',
            'phoneNumber',
            'createdAt',
            'updatedAt',
        ];

        foreach ($propertyNames as $propertyName) {
            $this->namingStrategy->propertyToColumnName($propertyName);
        }
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(100)]
    public function benchInflectorSingularize(): void
    {
        $pluralWords = [
            'users',
            'profiles',
            'articles',
            'comments',
            'categories',
            'tags',
            'children',
            'people',
        ];

        foreach ($pluralWords as $word) {
            $this->inflector->singularize($word);
        }
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(100)]
    public function benchInflectorPluralize(): void
    {
        $singularWords = [
            'user',
            'profile',
            'article',
            'comment',
            'category',
            'tag',
            'child',
            'person',
        ];

        foreach ($singularWords as $word) {
            $this->inflector->pluralize($word);
        }
    }
}
