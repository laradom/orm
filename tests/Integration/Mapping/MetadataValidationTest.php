<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration\Mapping;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Laradom\ORM\Enum\Attributes\GeneratorType;
use Laradom\ORM\Enum\Attributes\Types;
use Laradom\ORM\Mapping\Driver\AttributeDriver;
use Laradom\ORM\Mapping\Driver\AttributeHandler\MetadataProcessorFactory;
use Laradom\ORM\Mapping\EntityMetadataFactory;
use Laradom\ORM\Mapping\Naming\DefaultNamingStrategy;
use Laradom\ORM\Mapping\Processor\ColumnTypeProcessor;
use Laradom\ORM\Mapping\Processor\FieldNameProcessor;
use Laradom\ORM\Mapping\Processor\MetadataProcessorPipeline;
use Laradom\ORM\Mapping\Processor\PostProcessor\BidirectionalRelationshipPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\CascadeOperationsPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\IndexNamePostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\JoinColumnPostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\JoinTablePostProcessor;
use Laradom\ORM\Mapping\Processor\PostProcessor\UniqueConstraintNamePostProcessor;
use Laradom\ORM\Mapping\Processor\PrimaryKeyProcessor;
use Laradom\ORM\Mapping\Processor\TableNameProcessor;
use Laradom\ORM\Scanning\FileInfo;
use Laradom\ORM\Scanning\FileScanner;
use Laradom\ORM\Util\Inflector\EnglishInflector;
use Laradom\Tests\Integration\Mapping\TestEntity\Comment;
use Laradom\Tests\Integration\Mapping\TestEntity\Post;
use Laradom\Tests\Integration\Mapping\TestEntity\Profile;
use Laradom\Tests\Integration\Mapping\TestEntity\Tag;
use Laradom\Tests\Integration\Mapping\TestEntity\User;
use PHPUnit\Framework\TestCase;

class MetadataValidationTest extends TestCase
{
    private EntityMetadataFactory $metadataFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $namingStrategy = new DefaultNamingStrategy();
        $inflector = new EnglishInflector();

        $factory = new MetadataProcessorFactory();
        $driver = new AttributeDriver($factory->create());

        $processorPipeline = new MetadataProcessorPipeline([
            new TableNameProcessor($namingStrategy, $inflector),
            new FieldNameProcessor($namingStrategy),
            new PrimaryKeyProcessor(),
            new ColumnTypeProcessor(),
        ], [
            new BidirectionalRelationshipPostProcessor(),
            new CascadeOperationsPostProcessor(),
            new JoinTablePostProcessor($namingStrategy, $inflector),
            new JoinColumnPostProcessor($namingStrategy, $inflector),
            new IndexNamePostProcessor(),
            new UniqueConstraintNamePostProcessor(),
        ]);

        $store = new ArrayStore();
        $cache = new CacheRepository($store);

        $fileScanner = $this->createMock(FileScanner::class);

        $fileScanner->method('scan')
            ->willReturn([
                new FileInfo('User.php', '/path/to/User.php', User::class),
                new FileInfo('Profile.php', '/path/to/Profile.php', Profile::class),
                new FileInfo('Post.php', '/path/to/Post.php', Post::class),
                new FileInfo('Tag.php', '/path/to/Tag.php', Tag::class),
                new FileInfo('Comment.php', '/path/to/Comment.php', Comment::class),
            ]);

        $this->metadataFactory = new EntityMetadataFactory(
            $driver,
            $cache,
            $processorPipeline,
            $fileScanner,
            false,
            false,
        );
    }

    public function testAllEntitiesAreLoaded(): void
    {
        $allMetadata = $this->metadataFactory->getAllMetadata();

        $this->assertCount(5, $allMetadata);
        $this->assertArrayHasKey(User::class, $allMetadata);
        $this->assertArrayHasKey(Profile::class, $allMetadata);
        $this->assertArrayHasKey(Post::class, $allMetadata);
        $this->assertArrayHasKey(Tag::class, $allMetadata);
        $this->assertArrayHasKey(Comment::class, $allMetadata);
    }

    public function testUserEntityMetadata(): void
    {
        $allMetadata = $this->metadataFactory->getAllMetadata();
        $userMetadata = $allMetadata[User::class];

        $this->assertEquals('users', $userMetadata->getTableName());

        $this->assertNotNull($userMetadata->getPrimaryKey());
        $this->assertEquals('id', $userMetadata->getPrimaryKey()->getColumnName());

        $idField = $userMetadata->getFieldByColumnName('id');
        $this->assertEquals('id', $idField->getPropertyName());
        $this->assertEquals('id', $idField->getColumnName());
        $this->assertEquals(Types::INTEGER, $idField->getType());
        $this->assertTrue($idField->isPrimaryKey());
        $this->assertTrue($idField->getGeneratedFieldMetadata()->isGenerated());
        $this->assertEquals(GeneratorType::IDENTITY, $idField->getGeneratedFieldMetadata()->getGeneratorType());
        $this->assertNull($idField->getGeneratedFieldMetadata()->getGeneratedCustomClass());
        $this->assertNull($idField->getDefaultValue());
        $this->assertFalse($idField->hasDefaultValue());
        $this->assertNull($idField->getOptions()->getLength());
        $this->assertFalse($idField->getOptions()->isNullable());
        $this->assertTrue($idField->getOptions()->isUnique());
        $this->assertNull($idField->getOptions()->getPrecision());
        $this->assertNull($idField->getOptions()->getScale());
        $this->assertNull($idField->getOptions()->getColumnDefinition());

        $emailField = $userMetadata->getFieldByColumnName('email');
        $this->assertEquals('email', $emailField->getPropertyName());
        $this->assertEquals('email', $emailField->getColumnName());
        $this->assertEquals(Types::STRING, $emailField->getType());
        $this->assertFalse($emailField->isPrimaryKey());
        $this->assertNull($emailField->getGeneratedFieldMetadata());
        $this->assertNull($emailField->getDefaultValue());
        $this->assertFalse($emailField->hasDefaultValue());
        $this->assertEquals(255, $emailField->getOptions()->getLength());
        $this->assertFalse($emailField->getOptions()->isNullable());
        $this->assertTrue($emailField->getOptions()->isUnique());
        $this->assertNull($emailField->getOptions()->getPrecision());
        $this->assertNull($emailField->getOptions()->getScale());
        $this->assertNull($emailField->getOptions()->getColumnDefinition());

        $nameField = $userMetadata->getFieldByColumnName('name');
        $this->assertEquals('name', $nameField->getPropertyName());
        $this->assertEquals('name', $nameField->getColumnName());
        $this->assertEquals(Types::STRING, $nameField->getType());
        $this->assertFalse($nameField->isPrimaryKey());
        $this->assertNull($nameField->getGeneratedFieldMetadata());
        $this->assertEquals('user_name', $nameField->getDefaultValue());
        $this->assertTrue($nameField->hasDefaultValue());
        $this->assertEquals(255, $nameField->getOptions()->getLength());
        $this->assertFalse($nameField->getOptions()->isNullable());
        $this->assertFalse($nameField->getOptions()->isUnique());
        $this->assertNull($nameField->getOptions()->getPrecision());
        $this->assertNull($nameField->getOptions()->getScale());
        $this->assertNull($nameField->getOptions()->getColumnDefinition());

        $createdAtField = $userMetadata->getFieldByColumnName('created_at');
        $this->assertEquals('createdAt', $createdAtField->getPropertyName());
        $this->assertEquals('created_at', $createdAtField->getColumnName());
        $this->assertEquals(Types::DATETIME, $createdAtField->getType());
        $this->assertFalse($createdAtField->isPrimaryKey());
        $this->assertNull($createdAtField->getGeneratedFieldMetadata());
        $this->assertNull($createdAtField->getDefaultValue());
        $this->assertFalse($createdAtField->hasDefaultValue());
        $this->assertNull($createdAtField->getOptions()->getLength());
        $this->assertFalse($createdAtField->getOptions()->isNullable());
        $this->assertFalse($createdAtField->getOptions()->isUnique());
        $this->assertNull($createdAtField->getOptions()->getPrecision());
        $this->assertNull($createdAtField->getOptions()->getScale());
        $this->assertNull($createdAtField->getOptions()->getColumnDefinition());

        $userProfileRelation = $userMetadata->getRelationByFieldName('profile');
        $this->assertEquals(Profile::class, $userProfileRelation->getTargetEntity());
        $this->assertEquals('user', $userProfileRelation->getInversedBy());
        $this->assertNull($userProfileRelation->getMappedBy());

        $this->assertCount(1, $userProfileRelation->getJoinColumns());
        $joinColumn = $userProfileRelation->getJoinColumns()[0];
        $this->assertEquals('profile_id', $joinColumn->getName());
        $this->assertEquals('id', $joinColumn->getReferencedColumnName());
        $this->assertFalse($joinColumn->isNullable());
        $this->assertTrue($joinColumn->isUnique());

        $userPostsRelation = $userMetadata->getRelationByFieldName('posts');
        $this->assertEquals(Post::class, $userPostsRelation->getTargetEntity());
        $this->assertEquals('user', $userPostsRelation->getMappedBy());
        $this->assertNull($userPostsRelation->getInversedBy());
        $this->assertTrue($userPostsRelation->getCascade()->isPersist());
        $this->assertFalse($userPostsRelation->isOrphanRemoval());

        $userTagsRelation = $userMetadata->getRelationByFieldName('tags');
        $this->assertEquals(Tag::class, $userTagsRelation->getTargetEntity());
        $this->assertEquals('users', $userTagsRelation->getInversedBy());
        $this->assertNull($userTagsRelation->getMappedBy());
        $this->assertNotNull($userTagsRelation->getJoinTable());

        $this->assertEquals('users_tags', $userTagsRelation->getJoinTable()->getName());

        $joinTable = $userTagsRelation->getJoinTable();
        $this->assertCount(1, $joinTable->getJoinColumns());
        $this->assertCount(1, $joinTable->getInverseJoinColumns());

        $joinColumn = $joinTable->getJoinColumns()[0];
        $this->assertEquals('user_id', $joinColumn->getName());
        $this->assertEquals('id', $joinColumn->getReferencedColumnName());
        $this->assertFalse($joinColumn->isNullable());
        $this->assertFalse($joinColumn->isUnique());

        $inverseJoinColumn = $joinTable->getInverseJoinColumns()[0];
        $this->assertEquals('tag_id', $inverseJoinColumn->getName());
        $this->assertEquals('id', $inverseJoinColumn->getReferencedColumnName());
        $this->assertFalse($inverseJoinColumn->isNullable());
        $this->assertFalse($inverseJoinColumn->isUnique());
    }

    public function testProfileEntityMetadata(): void
    {
        $allMetadata = $this->metadataFactory->getAllMetadata();
        $profileMetadata = $allMetadata[Profile::class];

        $this->assertEquals('profiles', $profileMetadata->getTableName());

        $this->assertNotNull($profileMetadata->getPrimaryKey());
        $this->assertEquals('id', $profileMetadata->getPrimaryKey()->getColumnName());

        $idField = $profileMetadata->getFieldByColumnName('id');
        $this->assertEquals('id', $idField->getPropertyName());
        $this->assertEquals('id', $idField->getColumnName());
        $this->assertEquals(Types::INTEGER, $idField->getType());
        $this->assertTrue($idField->isPrimaryKey());
        $this->assertTrue($idField->getGeneratedFieldMetadata()->isGenerated());
        $this->assertEquals(GeneratorType::IDENTITY, $idField->getGeneratedFieldMetadata()->getGeneratorType());
        $this->assertNull($idField->getGeneratedFieldMetadata()->getGeneratedCustomClass());
        $this->assertNull($idField->getDefaultValue());
        $this->assertFalse($idField->hasDefaultValue());
        $this->assertNull($idField->getOptions()->getLength());
        $this->assertFalse($idField->getOptions()->isNullable());
        $this->assertTrue($idField->getOptions()->isUnique());
        $this->assertNull($idField->getOptions()->getPrecision());
        $this->assertNull($idField->getOptions()->getScale());
        $this->assertNull($idField->getOptions()->getColumnDefinition());

        $bioField = $profileMetadata->getFieldByColumnName('bio');
        $this->assertEquals('bio', $bioField->getPropertyName());
        $this->assertEquals('bio', $bioField->getColumnName());
        $this->assertEquals(Types::STRING, $bioField->getType());
        $this->assertFalse($bioField->isPrimaryKey());
        $this->assertNull($bioField->getGeneratedFieldMetadata());
        $this->assertNull($bioField->getDefaultValue());
        $this->assertFalse($bioField->hasDefaultValue());
        $this->assertEquals(255, $bioField->getOptions()->getLength());
        $this->assertFalse($bioField->getOptions()->isNullable());
        $this->assertFalse($bioField->getOptions()->isUnique());
        $this->assertNull($bioField->getOptions()->getPrecision());
        $this->assertNull($bioField->getOptions()->getScale());
        $this->assertNull($bioField->getOptions()->getColumnDefinition());

        $avatarField = $profileMetadata->getFieldByColumnName('avatar');
        $this->assertEquals('avatar', $avatarField->getPropertyName());
        $this->assertEquals('avatar', $avatarField->getColumnName());
        $this->assertEquals(Types::STRING, $avatarField->getType());
        $this->assertFalse($avatarField->isPrimaryKey());
        $this->assertNull($avatarField->getGeneratedFieldMetadata());
        $this->assertNull($avatarField->getDefaultValue());
        $this->assertTrue($avatarField->hasDefaultValue());
        $this->assertEquals(255, $avatarField->getOptions()->getLength());
        $this->assertTrue($avatarField->getOptions()->isNullable());
        $this->assertFalse($avatarField->getOptions()->isUnique());
        $this->assertNull($avatarField->getOptions()->getPrecision());
        $this->assertNull($avatarField->getOptions()->getScale());
        $this->assertNull($avatarField->getOptions()->getColumnDefinition());

        $profileUserRelation = $profileMetadata->getRelationByFieldName('user');
        $this->assertEquals(User::class, $profileUserRelation->getTargetEntity());
        $this->assertNull($profileUserRelation->getInversedBy());
        $this->assertEquals('profile', $profileUserRelation->getMappedBy());

        $this->assertCount(0, $profileUserRelation->getJoinColumns());
    }

    public function testPostEntityMetadata(): void
    {
        $allMetadata = $this->metadataFactory->getAllMetadata();
        $postMetadata = $allMetadata[Post::class];

        $this->assertEquals('posts', $postMetadata->getTableName());

        $this->assertNotNull($postMetadata->getPrimaryKey());
        $this->assertEquals('id', $postMetadata->getPrimaryKey()->getColumnName());

        $idField = $postMetadata->getFieldByColumnName('id');
        $this->assertEquals('id', $idField->getPropertyName());
        $this->assertEquals('id', $idField->getColumnName());
        $this->assertEquals(Types::INTEGER, $idField->getType());
        $this->assertTrue($idField->isPrimaryKey());
        $this->assertTrue($idField->getGeneratedFieldMetadata()->isGenerated());
        $this->assertEquals(GeneratorType::IDENTITY, $idField->getGeneratedFieldMetadata()->getGeneratorType());
        $this->assertNull($idField->getGeneratedFieldMetadata()->getGeneratedCustomClass());
        $this->assertNull($idField->getDefaultValue());
        $this->assertFalse($idField->hasDefaultValue());
        $this->assertNull($idField->getOptions()->getLength());
        $this->assertFalse($idField->getOptions()->isNullable());
        $this->assertTrue($idField->getOptions()->isUnique());
        $this->assertNull($idField->getOptions()->getPrecision());
        $this->assertNull($idField->getOptions()->getScale());
        $this->assertNull($idField->getOptions()->getColumnDefinition());

        $titleField = $postMetadata->getFieldByColumnName('title');
        $this->assertEquals('title', $titleField->getPropertyName());
        $this->assertEquals('title', $titleField->getColumnName());
        $this->assertEquals(Types::STRING, $titleField->getType());
        $this->assertFalse($titleField->isPrimaryKey());
        $this->assertNull($titleField->getGeneratedFieldMetadata());
        $this->assertNull($titleField->getDefaultValue());
        $this->assertFalse($titleField->hasDefaultValue());
        $this->assertEquals(255, $titleField->getOptions()->getLength());
        $this->assertFalse($titleField->getOptions()->isNullable());
        $this->assertFalse($titleField->getOptions()->isUnique());
        $this->assertNull($titleField->getOptions()->getPrecision());
        $this->assertNull($titleField->getOptions()->getScale());
        $this->assertNull($titleField->getOptions()->getColumnDefinition());

        $contentField = $postMetadata->getFieldByColumnName('content');
        $this->assertEquals('content', $contentField->getPropertyName());
        $this->assertEquals('content', $contentField->getColumnName());
        $this->assertEquals(Types::STRING, $contentField->getType());
        $this->assertFalse($contentField->isPrimaryKey());
        $this->assertNull($contentField->getGeneratedFieldMetadata());
        $this->assertNull($contentField->getDefaultValue());
        $this->assertFalse($contentField->hasDefaultValue());
        $this->assertEquals(255, $contentField->getOptions()->getLength());
        $this->assertFalse($contentField->getOptions()->isNullable());
        $this->assertFalse($contentField->getOptions()->isUnique());
        $this->assertNull($contentField->getOptions()->getPrecision());
        $this->assertNull($contentField->getOptions()->getScale());
        $this->assertNull($contentField->getOptions()->getColumnDefinition());

        $createdAtField = $postMetadata->getFieldByColumnName('created_at');
        $this->assertEquals('createdAt', $createdAtField->getPropertyName());
        $this->assertEquals('created_at', $createdAtField->getColumnName());
        $this->assertEquals(Types::DATETIME, $createdAtField->getType());
        $this->assertFalse($createdAtField->isPrimaryKey());
        $this->assertNull($createdAtField->getGeneratedFieldMetadata());
        $this->assertNull($createdAtField->getDefaultValue());
        $this->assertFalse($createdAtField->hasDefaultValue());
        $this->assertNull($createdAtField->getOptions()->getLength());
        $this->assertFalse($createdAtField->getOptions()->isNullable());
        $this->assertFalse($createdAtField->getOptions()->isUnique());
        $this->assertNull($createdAtField->getOptions()->getPrecision());
        $this->assertNull($createdAtField->getOptions()->getScale());
        $this->assertNull($createdAtField->getOptions()->getColumnDefinition());

        $postUserRelation = $postMetadata->getRelationByFieldName('user');
        $this->assertEquals(User::class, $postUserRelation->getTargetEntity());
        $this->assertNull($postUserRelation->getMappedBy());
        $this->assertEquals('posts', $postUserRelation->getInversedBy());

        $this->assertCount(1, $postUserRelation->getJoinColumns());
        $joinColumn = $postUserRelation->getJoinColumns()[0];
        $this->assertEquals('user_id', $joinColumn->getName());
        $this->assertEquals('id', $joinColumn->getReferencedColumnName());
        $this->assertTrue($joinColumn->isNullable());
        $this->assertFalse($joinColumn->isUnique());

        $postCommentsRelation = $postMetadata->getRelationByFieldName('comments');
        $this->assertEquals(Comment::class, $postCommentsRelation->getTargetEntity());
        $this->assertEquals('post', $postCommentsRelation->getMappedBy());
        $this->assertNull($postCommentsRelation->getInversedBy());
        $this->assertTrue($postCommentsRelation->getCascade()->isPersist());
        $this->assertTrue($postCommentsRelation->getCascade()->isRemove());
        $this->assertTrue($postCommentsRelation->isOrphanRemoval());
    }

    public function testTagEntityMetadata(): void
    {
        $allMetadata = $this->metadataFactory->getAllMetadata();
        $tagMetadata = $allMetadata[Tag::class];

        $this->assertEquals('tags', $tagMetadata->getTableName());

        $this->assertNotNull($tagMetadata->getPrimaryKey());
        $this->assertEquals('id', $tagMetadata->getPrimaryKey()->getColumnName());

        $idField = $tagMetadata->getFieldByColumnName('id');
        $this->assertEquals('id', $idField->getPropertyName());
        $this->assertEquals('id', $idField->getColumnName());
        $this->assertEquals(Types::INTEGER, $idField->getType());
        $this->assertTrue($idField->isPrimaryKey());
        $this->assertTrue($idField->getGeneratedFieldMetadata()->isGenerated());
        $this->assertEquals(GeneratorType::IDENTITY, $idField->getGeneratedFieldMetadata()->getGeneratorType());
        $this->assertNull($idField->getGeneratedFieldMetadata()->getGeneratedCustomClass());
        $this->assertNull($idField->getDefaultValue());
        $this->assertFalse($idField->hasDefaultValue());
        $this->assertNull($idField->getOptions()->getLength());
        $this->assertFalse($idField->getOptions()->isNullable());
        $this->assertTrue($idField->getOptions()->isUnique());
        $this->assertNull($idField->getOptions()->getPrecision());
        $this->assertNull($idField->getOptions()->getScale());
        $this->assertNull($idField->getOptions()->getColumnDefinition());

        $nameField = $tagMetadata->getFieldByColumnName('name');
        $this->assertEquals('name', $nameField->getPropertyName());
        $this->assertEquals('name', $nameField->getColumnName());
        $this->assertEquals(Types::STRING, $nameField->getType());
        $this->assertFalse($nameField->isPrimaryKey());
        $this->assertNull($nameField->getGeneratedFieldMetadata());
        $this->assertNull($nameField->getDefaultValue());
        $this->assertFalse($nameField->hasDefaultValue());
        $this->assertEquals(255, $nameField->getOptions()->getLength());
        $this->assertFalse($nameField->getOptions()->isNullable());
        $this->assertTrue($nameField->getOptions()->isUnique());
        $this->assertNull($nameField->getOptions()->getPrecision());
        $this->assertNull($nameField->getOptions()->getScale());
        $this->assertNull($nameField->getOptions()->getColumnDefinition());

        $tagUsersRelation = $tagMetadata->getRelationByFieldName('users');
        $this->assertEquals(User::class, $tagUsersRelation->getTargetEntity());
        $this->assertNull($tagUsersRelation->getInversedBy());
        $this->assertEquals('tags', $tagUsersRelation->getMappedBy());
    }

    public function testCommentEntityMetadata(): void
    {
        $allMetadata = $this->metadataFactory->getAllMetadata();
        $commentMetadata = $allMetadata[Comment::class];

        $this->assertEquals('comments', $commentMetadata->getTableName());

        $this->assertNotNull($commentMetadata->getPrimaryKey());
        $this->assertEquals('id', $commentMetadata->getPrimaryKey()->getColumnName());

        $idField = $commentMetadata->getFieldByColumnName('id');
        $this->assertEquals('id', $idField->getPropertyName());
        $this->assertEquals('id', $idField->getColumnName());
        $this->assertEquals(Types::INTEGER, $idField->getType());
        $this->assertTrue($idField->isPrimaryKey());
        $this->assertTrue($idField->getGeneratedFieldMetadata()->isGenerated());
        $this->assertEquals(GeneratorType::IDENTITY, $idField->getGeneratedFieldMetadata()->getGeneratorType());
        $this->assertNull($idField->getGeneratedFieldMetadata()->getGeneratedCustomClass());
        $this->assertNull($idField->getDefaultValue());
        $this->assertFalse($idField->hasDefaultValue());
        $this->assertNull($idField->getOptions()->getLength());
        $this->assertFalse($idField->getOptions()->isNullable());
        $this->assertTrue($idField->getOptions()->isUnique());
        $this->assertNull($idField->getOptions()->getPrecision());
        $this->assertNull($idField->getOptions()->getScale());
        $this->assertNull($idField->getOptions()->getColumnDefinition());

        $contentField = $commentMetadata->getFieldByColumnName('content');
        $this->assertEquals('content', $contentField->getPropertyName());
        $this->assertEquals('content', $contentField->getColumnName());
        $this->assertEquals(Types::STRING, $contentField->getType());
        $this->assertFalse($contentField->isPrimaryKey());
        $this->assertNull($contentField->getGeneratedFieldMetadata());
        $this->assertNull($contentField->getDefaultValue());
        $this->assertFalse($contentField->hasDefaultValue());
        $this->assertEquals(255, $contentField->getOptions()->getLength());
        $this->assertFalse($contentField->getOptions()->isNullable());
        $this->assertFalse($contentField->getOptions()->isUnique());
        $this->assertNull($contentField->getOptions()->getPrecision());
        $this->assertNull($contentField->getOptions()->getScale());
        $this->assertNull($contentField->getOptions()->getColumnDefinition());

        $createdAtField = $commentMetadata->getFieldByColumnName('created_at');
        $this->assertEquals('createdAt', $createdAtField->getPropertyName());
        $this->assertEquals('created_at', $createdAtField->getColumnName());
        $this->assertEquals(Types::DATETIME, $createdAtField->getType());
        $this->assertFalse($createdAtField->isPrimaryKey());
        $this->assertNull($createdAtField->getGeneratedFieldMetadata());
        $this->assertNull($createdAtField->getDefaultValue());
        $this->assertFalse($createdAtField->hasDefaultValue());
        $this->assertNull($createdAtField->getOptions()->getLength());
        $this->assertFalse($createdAtField->getOptions()->isNullable());
        $this->assertFalse($createdAtField->getOptions()->isUnique());
        $this->assertNull($createdAtField->getOptions()->getPrecision());
        $this->assertNull($createdAtField->getOptions()->getScale());
        $this->assertNull($createdAtField->getOptions()->getColumnDefinition());

        $commentPostRelation = $commentMetadata->getRelationByFieldName('post');
        $this->assertEquals(Post::class, $commentPostRelation->getTargetEntity());
        $this->assertNull($commentPostRelation->getMappedBy());
        $this->assertEquals('comments', $commentPostRelation->getInversedBy());

        $this->assertCount(1, $commentPostRelation->getJoinColumns());
        $joinColumn = $commentPostRelation->getJoinColumns()[0];
        $this->assertEquals('post_id', $joinColumn->getName());
        $this->assertEquals('id', $joinColumn->getReferencedColumnName());
        $this->assertFalse($joinColumn->isNullable());
        $this->assertFalse($joinColumn->isUnique());
    }
}
