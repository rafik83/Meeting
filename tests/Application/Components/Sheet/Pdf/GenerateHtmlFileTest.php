<?php

namespace Proximum\Vimeet\Tests\Application\Components\Sheet\Pdf;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtmlFile;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UniqidGenerator;

class GenerateHtmlFileTest extends TestCase
{
    public function testGenerateFile()
    {
        $dateTime = new \DateTime();
        $content = '<html><head></head><body>fiche A</body>';

        $uniqidGenerator = $this->prophesize(UniqidGenerator::class);
        $localFileStorageAdapter = $this->prophesize(FileStorageInterface::class);
        $fileRepository = $this->prophesize(FileRepositoryInterface::class);

        $uniqidGenerator->generate()->willReturn('test');
        $localFileStorageAdapter
            ->create($content, 'test_generate_sheets_pdf.html', '/tmp')
            ->shouldBeCalled()
            ->willReturn('/tmp/test_generate_sheets_pdf.html')
        ;

        $file = new File('/tmp/test_generate_sheets_pdf.html', $dateTime);

        $fileRepository->add($file)->shouldBeCalled();

        $generateHtmlFile = new GenerateHtmlFile(
            $uniqidGenerator->reveal(),
            $localFileStorageAdapter->reveal(),
            $fileRepository->reveal(),
            '/tmp',
            $dateTime
        );

        $generateHtmlFile->generateFile($content);
    }
}
