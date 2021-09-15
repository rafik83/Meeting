<?php

namespace Proximum\Vimeet\Tests\Domain\File;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class FileFactoryTest extends TestCase
{
    public function test_create_and_persist_file()
    {
        $dateTime = new \DateTime();
        $fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $fileRepository
            ->add(new File('/path/to/file.xml', $dateTime, 'unknown'))
            ->shouldBeCalled()
        ;

        $fileFactory = new FileFactory($fileRepository->reveal(), $dateTime);
        $result = $fileFactory->createAndPersistFile('/path/to/file.xml');
        $this->assertEquals($result->getCreatedAt(), $dateTime);
        $this->assertEquals($result->getPath(), '/path/to/file.xml');
    }
}
