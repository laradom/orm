<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Mapping\Naming;

use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DefaultNamingStrategyTest extends TestCase
{
    private DefaultNamingStrategy $namingStrategy;

    protected function setUp(): void
    {
        $this->namingStrategy = new DefaultNamingStrategy();
    }

    #[DataProvider('classToTableNameProvider')]
    public function testClassToTableName(string $className, string $expectedTableName): void
    {
        $tableName = $this->namingStrategy->classToTableName($className);
        $this->assertEquals($expectedTableName, $tableName);
    }

    #[DataProvider('propertyToColumnNameProvider')]
    public function testPropertyToColumnName(string $propertyName, string $expectedColumnName): void
    {
        $columnName = $this->namingStrategy->propertyToColumnName($propertyName);
        $this->assertEquals($expectedColumnName, $columnName);
    }

    public function testReferenceColumnName(): void
    {
        $this->assertEquals('id', $this->namingStrategy->referenceColumnName());
    }

    #[DataProvider('joinColumnNameProvider')]
    public function testJoinColumnName(string $propertyName, string $expectedColumnName): void
    {
        $columnName = $this->namingStrategy->joinColumnName($propertyName);
        $this->assertEquals($expectedColumnName, $columnName);
    }

    #[DataProvider('joinTableNameProvider')]
    public function testJoinTableName(string $sourceEntity, string $targetEntity, string $expectedTableName): void
    {
        $tableName = $this->namingStrategy->joinTableName($sourceEntity, $targetEntity);
        $this->assertEquals($expectedTableName, $tableName);
    }

    #[DataProvider('joinKeyColumnNameProvider')]
    public function testJoinKeyColumnName(string $entityName, string $expectedColumnName): void
    {
        $columnName = $this->namingStrategy->joinKeyColumnName($entityName);
        $this->assertEquals($expectedColumnName, $columnName);
    }

    public static function classToTableNameProvider(): array
    {
        return [
            'simple class name' => ['User', 'user'],
            'camel case class name' => ['UserProfile', 'user_profile'],
            'namespaced class' => ['App\Models\User', 'user'],
            'namespaced camel case' => ['App\Models\UserProfile', 'user_profile'],
        ];
    }

    public static function propertyToColumnNameProvider(): array
    {
        return [
            'simple property' => ['name', 'name'],
            'camel case property' => ['firstName', 'first_name'],
            'property with underscore' => ['user_name', 'user_name'],
            'property with multiple camel case' => ['userFirstName', 'user_first_name'],
            'property with numbers' => ['column1Value', 'column1_value'],
            'property with uppercase' => ['URL', 'u_r_l'],
            'property with mixed case' => ['APIKey', 'a_p_i_key'],
        ];
    }

    public static function joinColumnNameProvider(): array
    {
        return [
            'simple property' => ['user', 'user_id'],
            'camel case property' => ['userProfile', 'user_profile_id'],
        ];
    }

    public static function joinTableNameProvider(): array
    {
        return [
            'simple entities' => ['User', 'Role', 'user_role'],
            'camel case entities' => ['UserProfile', 'RolePermission', 'user_profile_role_permission'],
            'namespaced entities' => ['App\Models\User', 'App\Models\Role', 'user_role'],
        ];
    }

    public static function joinKeyColumnNameProvider(): array
    {
        return [
            'simple entity' => ['User', 'user_id'],
            'camel case entity' => ['UserProfile', 'user_profile_id'],
            'namespaced entity' => ['App\Models\User', 'user_id'],
        ];
    }
}
