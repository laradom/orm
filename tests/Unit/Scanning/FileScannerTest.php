<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Scanning;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Laradom\ORM\Scanning\FileScanner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

class FileScannerTest extends TestCase
{
    private Filesystem $filesystem;
    private FileScanner $scanner;
    private array $testPaths = ['/path/to/entities'];

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->scanner = new FileScanner($this->filesystem, $this->testPaths);
    }

    public function testScanWithEmptyPaths(): void
    {
        $scanner = new FileScanner($this->filesystem, []);
        $result = $scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithNonExistentDirectory(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(false);

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithNoPhpFiles(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(true);

        $this->filesystem->method('allFiles')
            ->willReturn([
                $this->createFileMock('file1.txt', 'content', 'txt'),
                $this->createFileMock('file2.json', 'content', 'json'),
            ]);

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithValidPhpFilesButNoClasses(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(true);

        $file1 = $this->createFileMock(
            'script.php',
            '<?php echo "Hello World";',
            'php',
            '/path/to/entities/script.php',
        );

        $this->filesystem->method('allFiles')
            ->willReturn([$file1]);

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithInvalidPhpSyntax(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(true);

        $file1 = $this->createFileMock(
            'invalid.php',
            '<?php class Invalid { unclosed bracket',
            'php',
            '/path/to/entities/invalid.php',
        );

        $this->filesystem->method('allFiles')
            ->willReturn([$file1]);

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithMixedFileTypes(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(true);

        $phpFile = $this->createFileMock(
            'script.php',
            '<?php // PHP file',
            'php',
        );

        $txtFile = $this->createFileMock(
            'file.txt',
            'Text file content',
            'txt',
        );

        $this->filesystem->method('allFiles')
            ->willReturn([$phpFile, $txtFile]);

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithMultiplePaths(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturnMap([
                ['/path1', true],
                ['/path2', true],
            ]);

        $this->filesystem->method('allFiles')
            ->willReturn([]);

        $scanner = new FileScanner($this->filesystem, ['/path1', '/path2']);
        $result = $scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithExceptionInGetFiles(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(true);

        $this->filesystem->method('allFiles')
            ->willThrowException(new Exception('Test exception'));

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithValidPhpFileAndExistingClass(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(true);

        $file = $this->createFileMock(
            'User.php',
            '<?php namespace App\Entity; class User {}',
            'php',
            '/path/to/entities/User.php',
        );

        $this->filesystem->method('allFiles')
            ->willReturn([$file]);

        $className = 'App\Entity\User';

        if (!class_exists($className)) {
            eval('namespace App\Entity; class User {}');
        }

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('User.php', $result[0]->getFilename());
        $this->assertEquals('/path/to/entities/User.php', $result[0]->getPathname());
        $this->assertEquals($className, $result[0]->getClassName());
    }

    public function testScanWithFileWithoutNamespace(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(true);

        $file = $this->createFileMock(
            'NoNamespace.php',
            '<?php class NoNamespace {}',
            'php',
            '/path/to/entities/NoNamespace.php',
        );

        $this->filesystem->method('allFiles')
            ->willReturn([$file]);

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithFileWithoutClass(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(true);

        $file = $this->createFileMock(
            'NoClass.php',
            '<?php namespace App\Entity; function test() {}',
            'php',
            '/path/to/entities/NoClass.php',
        );

        $this->filesystem->method('allFiles')
            ->willReturn([$file]);

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testScanWithExceptionInGetContents(): void
    {
        $this->filesystem->method('isDirectory')
            ->willReturn(true);

        $file = $this->createMock(SplFileInfo::class);
        $file->method('getExtension')
            ->willReturn('php');
        $file->method('getContents')
            ->willThrowException(new Exception('Cannot read file'));

        $this->filesystem->method('allFiles')
            ->willReturn([$file]);

        $result = $this->scanner->scan();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    private function createFileMock(string $filename, string $content, string $extension, ?string $pathname = null): SplFileInfo
    {
        $file = $this->createMock(SplFileInfo::class);

        $file->method('getFilename')
            ->willReturn($filename);

        $file->method('getContents')
            ->willReturn($content);

        $file->method('getExtension')
            ->willReturn($extension);

        $file->method('getPathname')
            ->willReturn($pathname ?? '/path/to/' . $filename);

        return $file;
    }
}
