# Laradom ORM

Реализация паттерна Data Mapper поверх Eloquent Query Builder для Laravel.

[![English](https://img.shields.io/badge/language-English-blue.svg)](README.md)
[![Русский](https://img.shields.io/badge/язык-Русский-red.svg)](README.ru.md)

![CI](https://github.com/laradom/orm/actions/workflows/ci.yml/badge.svg)
![Совместимость](https://github.com/laradom/orm/actions/workflows/compatibility.yml/badge.svg)

## Описание

Laradom ORM представляет собой реализацию паттерна Data Mapper поверх Eloquent Query Builder для Laravel. Библиотека позволяет работать с базой данных через объекты-сущности, которые не зависят от слоя хранения данных.

## Основные компоненты

1. **Атрибуты (Attributes)**
   - Entity - помечает класс как сущность
   - Table - указывает имя таблицы
   - Column - определяет свойства колонки
   - Id, GeneratedValue - для первичных ключей
   - ManyToOne, OneToMany, OneToOne, ManyToMany - для отношений

2. **Маппинг (Mapping)**
   - EntityMetadata - хранит метаданные о классе/сущности
   - EntityMetadataFactory - создает и кэширует метаданные
   - Driver - система драйверов для загрузки метаданных
   - AttributeHandler - обработчики атрибутов
   - NamingStrategy - стратегия именования таблиц и колонок

3. **Коллекции (Collection)**
   - Collection - интерфейс для коллекций
   - ArrayCollection - базовая реализация коллекции на основе массива
   - EntityCollection - коллекция сущностей с отслеживанием изменений

## Требования

- PHP 8.1+
- Laravel 10+
- Composer

## Установка

```bash
composer require laradom/orm
```

## Использование

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

    // геттеры и сеттеры
}
```

## Разработка

### Запуск тестов

```bash
vendor/bin/phpunit
```

### Проверка стиля кода

```bash
vendor/bin/php-cs-fixer fix
```

### Статический анализ

```bash
vendor/bin/phpstan analyse
```

## CI/CD

Проект использует GitHub Actions для непрерывной интеграции и проверки качества кода. При каждом пуше и пул-реквесте запускаются следующие проверки:

- Проверка совместимости composer.json
- Проверка безопасности зависимостей
- Проверка стиля кода (PHP CS Fixer)
- Статический анализ кода (PHPStan)
- Запуск тестов (PHPUnit)
- Проверка совместимости с разными версиями PHP и Laravel

## Лицензия

MIT
