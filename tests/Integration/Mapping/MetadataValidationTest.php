<?php

declare(strict_types=1);

namespace Laradom\Tests\Integration\Mapping;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
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

    public function testValidEntityMetadata(): void
    {
        $allMetadata = $this->metadataFactory->getAllMetadata();

        $this->assertCount(5, $allMetadata);
        $this->assertArrayHasKey(User::class, $allMetadata);
        $this->assertArrayHasKey(Profile::class, $allMetadata);
        $this->assertArrayHasKey(Post::class, $allMetadata);
        $this->assertArrayHasKey(Tag::class, $allMetadata);
        $this->assertArrayHasKey(Comment::class, $allMetadata);

        $userMetadata = $allMetadata[User::class];
        $profileMetadata = $allMetadata[Profile::class];
        $postMetadata = $allMetadata[Post::class];
        $tagMetadata = $allMetadata[Tag::class];
        $commentMetadata = $allMetadata[Comment::class];

        $this->assertEquals('users', $userMetadata->getTableName());
        $this->assertEquals('profiles', $profileMetadata->getTableName());
        $this->assertEquals('posts', $postMetadata->getTableName());
        $this->assertEquals('tags', $tagMetadata->getTableName());
        $this->assertEquals('comments', $commentMetadata->getTableName());

        $this->assertNotNull($userMetadata->getPrimaryKey());
        $this->assertEquals('id', $userMetadata->getPrimaryKey()->getColumnName());
        $this->assertNotNull($profileMetadata->getPrimaryKey());
        $this->assertEquals('id', $profileMetadata->getPrimaryKey()->getColumnName());
        $this->assertNotNull($postMetadata->getPrimaryKey());
        $this->assertEquals('id', $postMetadata->getPrimaryKey()->getColumnName());
        $this->assertNotNull($tagMetadata->getPrimaryKey());
        $this->assertEquals('id', $tagMetadata->getPrimaryKey()->getColumnName());
        $this->assertNotNull($commentMetadata->getPrimaryKey());
        $this->assertEquals('id', $commentMetadata->getPrimaryKey()->getColumnName());

        // User -> Profile (OneToOne)
        $userProfileRelation = $userMetadata->getRelationByFieldName('profile');
        $this->assertEquals(Profile::class, $userProfileRelation->getTargetEntity());
        $this->assertEquals('user', $userProfileRelation->getInversedBy());
        $this->assertNull($userProfileRelation->getMappedBy());

        $this->assertCount(1, $userProfileRelation->getJoinColumns());
        $joinColumn = $userProfileRelation->getJoinColumns()[0];
        $this->assertEquals('profile_id', $joinColumn->getName());
        $this->assertEquals('id', $joinColumn->getReferencedColumnName());
        $this->assertFalse($joinColumn->isNullable());
        $this->assertFalse($joinColumn->isUnique());

        // Profile -> User (OneToOne)
        $profileUserRelation = $profileMetadata->getRelationByFieldName('user');
        $this->assertEquals(User::class, $profileUserRelation->getTargetEntity());
        $this->assertNull($profileUserRelation->getInversedBy());
        $this->assertEquals('profile', $profileUserRelation->getMappedBy());

        $this->assertCount(1, $profileUserRelation->getJoinColumns());
        $joinColumn = $profileUserRelation->getJoinColumns()[0];
        $this->assertEquals('user_id', $joinColumn->getName());
        $this->assertEquals('id', $joinColumn->getReferencedColumnName());
        $this->assertTrue($joinColumn->isNullable());
        $this->assertFalse($joinColumn->isUnique());

        // User -> Posts (OneToMany)
        $userPostsRelation = $userMetadata->getRelationByFieldName('posts');
        $this->assertEquals(Post::class, $userPostsRelation->getTargetEntity());
        $this->assertEquals('user', $userPostsRelation->getMappedBy());
        $this->assertNull($userPostsRelation->getInversedBy());
        $this->assertTrue($userPostsRelation->getCascade()->isPersist());
        $this->assertTrue($userPostsRelation->isOrphanRemoval());

        // Post -> User (ManyToOne)
        $postUserRelation = $postMetadata->getRelationByFieldName('user');
        $this->assertEquals(User::class, $postUserRelation->getTargetEntity());
        $this->assertNull($postUserRelation->getMappedBy());
        $this->assertEquals('posts', $postUserRelation->getInversedBy());

        $this->assertCount(1, $postUserRelation->getJoinColumns());
        $joinColumn = $postUserRelation->getJoinColumns()[0];
        $this->assertEquals('user_id', $joinColumn->getName());
        $this->assertEquals('id', $joinColumn->getReferencedColumnName());
        $this->assertFalse($joinColumn->isNullable());
        $this->assertFalse($joinColumn->isUnique());

        // User -> Tags (ManyToMany)
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

        // Tag -> Users (ManyToMany)
        $tagUsersRelation = $tagMetadata->getRelationByFieldName('users');
        $this->assertEquals(User::class, $tagUsersRelation->getTargetEntity());
        $this->assertNull($tagUsersRelation->getInversedBy());
        $this->assertEquals('tags', $tagUsersRelation->getMappedBy());

        // Post -> Comments (OneToMany)
        $postCommentsRelation = $postMetadata->getRelationByFieldName('comments');
        $this->assertEquals(Comment::class, $postCommentsRelation->getTargetEntity());
        $this->assertEquals('post', $postCommentsRelation->getMappedBy());
        $this->assertNull($postCommentsRelation->getInversedBy());

        // Comment -> Post (ManyToOne)
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
