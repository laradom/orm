# Laradom ORM

Data Mapper pattern implementation over Eloquent Query Builder for Laravel.

[![English](https://img.shields.io/badge/language-English-blue.svg)](README.md)
[![Русский](https://img.shields.io/badge/язык-Русский-red.svg)](README.ru.md)

![CI](https://github.com/laradom/orm/actions/workflows/ci.yml/badge.svg)
![Compatibility](https://github.com/laradom/orm/actions/workflows/compatibility.yml/badge.svg)

## Description

Laradom ORM is an implementation of the Data Mapper pattern over Eloquent Query Builder for Laravel. The library allows you to work with the database through entity objects that are independent of the data storage layer.

## Main Components

1. **Attributes**
   - Entity - marks a class as an entity
   - Table - specifies the table name
   - Column - defines column properties
   - Id, GeneratedValue - for primary keys
   - ManyToOne, OneToMany, OneToOne, ManyToMany - for relationships

2. **Mapping**
   - EntityMetadata - stores metadata about the class/entity
   - EntityMetadataFactory - creates and caches metadata
   - Driver - driver system for loading metadata
   - AttributeHandler - attribute handlers
   - NamingStrategy - naming strategy for tables and columns

3. **Collections**
   - Collection - interface for collections
   - ArrayCollection - basic array-based collection implementation
   - EntityCollection - entity collection with change tracking

## Requirements

- PHP 8.1+
- Laravel 10+
- Composer

## Installation

```bash
composer require laradom/orm
```

## Usage

```php
<?php

namespace App\Entities;

use Laradom\ORM\Attributes\Column;
use Laradom\ORM\Attributes\Entity;
use Laradom\ORM\Attributes\Id;
use Laradom\ORM\Attributes\Table;
use Laradom\ORM\Enum\Attributes\Types;

#[Entity]
#[Table(name: 'users')]
class User
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    private int $id;

    #[Column(name: 'name', type: Types::STRING, length: 255)]
    private string $name;

    #[Column(name: 'email', type: Types::STRING, length: 255)]
    private string $email;

    // getters and setters
}
```

## Development

### Running Tests

```bash
vendor/bin/phpunit
```

### Code Style Checking

```bash
vendor/bin/php-cs-fixer fix
```

### Static Analysis

```bash
vendor/bin/phpstan analyse
```

## CI/CD

The project uses GitHub Actions for continuous integration and code quality checks. The following checks are run on each push and pull request:

- Composer.json compatibility check
- Dependencies security check
- Code style check (PHP CS Fixer)
- Static code analysis (PHPStan)
- Running tests (PHPUnit)
- Compatibility check with different versions of PHP and Laravel

## License

MIT
