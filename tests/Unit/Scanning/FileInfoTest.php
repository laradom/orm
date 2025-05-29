<?php

declare(strict_types=1);

namespace Laradom\Tests\Unit\Scanning;

use Laradom\ORM\Scanning\FileInfo;
use PHPUnit\Framework\TestCase;

class FileInfoTest extends TestCase
{
    private FileInfo $fileInfo;
    private string $fileName = 'TestFile.php';
    private string $pathname = '/path/to/TestFile.php';
    private string $className = 'App\Entity\TestEntity';

    protected function setUp(): void
    {
        $this->fileInfo = new FileInfo(
            $this->fileName,
            $this->pathname,
            $this->className,
        );
    }

    public function testGetFileName(): void
    {
        $this->assertEquals($this->fileName, $this->fileInfo->getFileName());
    }

    public function testGetPathname(): void
    {
        $this->assertEquals($this->pathname, $this->fileInfo->getPathname());
    }

    public function testGetClassName(): void
    {
        $this->assertEquals($this->className, $this->fileInfo->getClassName());
    }
}
