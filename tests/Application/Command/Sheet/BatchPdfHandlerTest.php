<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ThirdParty\Jenkins\BuildCreatorInterface;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdf;
use Proximum\Vimeet\Application\Command\Sheet\BatchPdfHandler;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtml;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtmlFile;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchPdfHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $file   = $this->prophesize(File::class);

        $file->getPath()->willReturn('/path.html');
        $file->getHash()->willReturn('hash');
        $file->getId()->willReturn(19);

        $event->getId()->willReturn(12);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $sheets = [
            $sheet1->reveal(),
            $sheet2->reveal(),
            $sheet3->reveal(),
        ];

        $eventRepository  = $this->prophesize(EventRepositoryInterface::class);
        $sheetRepository  = $this->prophesize(SheetRepositoryInterface::class);
        $generateHtml     = $this->prophesize(GenerateHtml::class);
        $generateHtmlFile = $this->prophesize(GenerateHtmlFile::class);
        $buildCreator     = $this->prophesize(BuildCreatorInterface::class);

        $eventRepository->getById(12)->shouldBeCalled()->willReturn($event->reveal());
        $sheetRepository->getSheetsByIdOrdered([9, 11, 13, 17], 'alphabetical')->shouldBeCalled()->willReturn($sheets);
        $generateHtml->setContext('https', 'admin.vimeet.proximum.dev')->shouldBeCalled();
        $generateHtml->printSheets($event->reveal(), $sheets, 'fr')->shouldBeCalled()->willReturn('ficheAficheBficheC');
        $generateHtmlFile->generateFile('ficheAficheBficheC')->shouldBeCalled()->willReturn($file->reveal());
        $generateHtmlFile->getFileDirectory()->shouldBeCalled()->willReturn('/tmp');
        $buildCreator
            ->create(
                'build_name',
                [
                    'INPUT'         => '/tmp/path.html',
                    'OUTPUT'        => '/tmp/path.html.pdf',
                    'EVENTID'       => 12,
                    'EMAIL'         => 'email@example.net',
                    'LOCALE'        => 'fr',
                    'INPUT_FILE_ID' => 19,
                ]
            )
            ->shouldBeCalled()
        ;

        $command = new BatchPdf(12, [9, 11, 13, 17], 'email@example.net', 'fr', 'alphabetical');
        $handler = new BatchPdfHandler(
            $eventRepository->reveal(),
            $sheetRepository->reveal(),
            $generateHtmlFile->reveal(),
            $generateHtml->reveal(),
            $buildCreator->reveal(),
            'build_name',
            'admin.vimeet.proximum.dev',
            'https'
        );

        $handler->handle($command);
    }
}
