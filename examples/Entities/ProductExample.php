<?php

declare(strict_types=1);

namespace Laradom\Examples\Entities;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\GeneratedValue;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\Index;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Attributes\UniqueConstraint;
use Laradom\ORM\Enum\Attributes\GeneratorType;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
#[Table(name: 'products')]
#[Index(columns: ['name'])]
#[Index(name: 'idx_product_price', columns: ['price'])]
#[Index(name: 'idx_product_category_name', columns: ['name'])]
#[UniqueConstraint(columns: ['sku'])]
#[UniqueConstraint(columns: ['name'])]
class ProductExample
{
    #[Id]
    #[Column(name: 'id', type: Types::INTEGER)]
    #[GeneratedValue(strategy: GeneratorType::AUTO)]
    private int $id;

    #[Column(name: 'name', type: Types::STRING, length: 255)]
    private string $name;

    #[Column(name: 'sku', type: Types::STRING, length: 50)]
    private string $sku;

    #[Column(name: 'price', type: Types::FLOAT, precision: 10, scale: 2)]
    private float $price;

    #[Column(name: 'description', type: Types::STRING, nullable: true)]
    private ?string $description = null;
}
