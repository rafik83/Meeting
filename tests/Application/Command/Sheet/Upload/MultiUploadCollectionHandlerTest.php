<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Upload;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Sheet\Upload\MultiUploadCollection;
use Proximum\Vimeet\Application\Command\Sheet\Upload\MultiUploadCollectionHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Nomenclature\Id\UniquIdGenerator;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultiUploadCollectionHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $sharedUploadedFilesPath = '/var/shared';

        $file4 = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'png'])
            ->getMock();

        $multiUpload1 = $this->prophesize(MultiUploadObject::class);
        $multiUpload1->getDefaultValues()->shouldBeCalled()->willReturn(['uniqid1', 'title1', '/var/uploads/1.png']);
        $multiUpload1->getFile()->shouldBeCalled()->willReturn(null);

        $multiUpload2 = $this->prophesize(MultiUploadObject::class);
        $multiUpload2->getDefaultValues()->shouldBeCalled()->willReturn(['uniqid2', 'title2', '/var/uploads/2.png']);
        $multiUpload2->getFile()->shouldBeCalled()->willReturn(null);

        $multiUpload3 = $this->prophesize(MultiUploadObject::class);
        $multiUpload3->getUniqId()->shouldBeCalled()->willReturn('uniqid3');
        $multiUpload3->getPath()->shouldBeCalled()->willReturn('/var/uploads/3.png');

        $multiUpload4 = $this->prophesize(MultiUploadObject::class);
        $multiUpload4->getPath()->shouldBeCalled()->willReturn(null);
        $multiUpload4->getFile()->shouldBeCalled()->willReturn($file4);
        $multiUpload4->getUniqId()->shouldBeCalled()->willReturn(null);
        $multiUpload4->getTitle()->shouldBeCalled()->willReturn('title3');

        $initialCollection = $this->prophesize(MultiUploadCollectionObject::class);
        $initialCollection->getUploads()
            ->shouldBeCalled()
            ->willReturn([$multiUpload3->reveal()]);

        $savedCollection = $this->prophesize(MultiUploadCollectionObject::class);
        $savedCollection->getUploads()
            ->shouldBeCalled()
            ->willReturn([
                $multiUpload1->reveal(),
                $multiUpload2->reveal(),
                $multiUpload4->reveal()
            ]);
        $savedCollection->getUploadsIndexedByUniqid()
            ->shouldBeCalled()
            ->willReturn([
                'uniqid1' => $multiUpload1->reveal(),
                'uniqid2' => $multiUpload2->reveal(),
            ]);

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->remove($sharedUploadedFilesPath.'/var/uploads/3.png')->shouldBeCalled();
        $fileStorage->upload(Argument::any(), $sharedUploadedFilesPath)->shouldBeCalled();

        $uniquIdGenerator = $this->prophesize(UniquIdGenerator::class);
        $uniquIdGenerator->generate()
            ->shouldBeCalled()
            ->willReturn('uniqid4');

        $handler = new MultiUploadCollectionHandler($fileStorage->reveal(), $uniquIdGenerator->reveal(), $sharedUploadedFilesPath);
        $handler->handle(new MultiUploadCollection($initialCollection->reveal(), $savedCollection->reveal()));
    }
}
