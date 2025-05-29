<?php

declare(strict_types=1);

namespace Laradom\Benchmarks;

use Illuminate\Filesystem\Filesystem;
use Laradom\ORM\Scanning\FileScanner;
use PhpBench\Attributes as Bench;
use Psr\Log\NullLogger;

class FileScannerBench
{
    private FileScanner $fileScanner;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->fileScanner = BenchmarkContext::getComponent('fileScanner');
        $this->filesystem = BenchmarkContext::getComponent('filesystem');
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(5)]
    public function benchScanSingleDirectory(): void
    {
        $scanner = new FileScanner(
            $this->filesystem,
            [__DIR__ . '/../tests/Integration/Mapping/TestEntity'],
            new NullLogger(),
        );

        $scanner->scan();
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(5)]
    public function benchScanMultipleDirectories(): void
    {
        $scanner = new FileScanner(
            $this->filesystem,
            [
                __DIR__ . '/../tests/Integration/Mapping/TestEntity',
                __DIR__ . '/../src/Mapping',
                __DIR__ . '/../src/Mapping/Driver',
            ],
            new NullLogger(),
        );

        $scanner->scan();
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(5)]
    public function benchScanWithNamespaceFilter(): void
    {
        $scanner = new FileScanner(
            $this->filesystem,
            [__DIR__ . '/../src'],
            new NullLogger(),
        );

        $scanner->scan();
    }

    #[Bench\Iterations(10)]
    #[Bench\Revs(5)]
    public function benchScanWithCaching(): void
    {
        $this->fileScanner->scan();
    }
}
