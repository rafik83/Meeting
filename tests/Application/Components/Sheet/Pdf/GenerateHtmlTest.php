<?php

namespace Proximum\Vimeet\Tests\Application\Components\Sheet\Pdf;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtml;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfosHelper;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateData;

class GenerateHtmlTest extends TestCase
{
    public function testPrintSheets()
    {
        $locale = 'fr';
        $event = $this->prophesize(Event::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheets = [
            $sheet1->reveal(),
            $sheet2->reveal(),
            $sheet3->reveal(),
        ];
        $templateData1 = $this->prophesize(TemplateData::class);
        $templateData2 = $this->prophesize(TemplateData::class);
        $templateData3 = $this->prophesize(TemplateData::class);

        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);
        $user4 = $this->prophesize(User::class);
        $user5 = $this->prophesize(User::class);
        $sheet1->getUsers()->willReturn([$user1->reveal()]);
        $sheet2->getUsers()->willReturn([$user2->reveal(), $user3->reveal()]);
        $sheet3->getUsers()->willReturn([$user4->reveal(), $user5->reveal()]);

        $nomenclatures1 = [$this->prophesize(Nomenclature::class)];
        $nomenclatures2 = [$this->prophesize(Nomenclature::class), $this->prophesize(Nomenclature::class)];
        $nomenclatures3 = [$this->prophesize(Nomenclature::class), $this->prophesize(Nomenclature::class)];
        $participants1 = [$this->prophesize(Participant::class)];
        $participants2 = [$this->prophesize(Participant::class), $this->prophesize(Participant::class)];
        $participants3 = [$this->prophesize(Participant::class)];
        $taggedData1 = [];
        $taggedData2 = ['sheet_data' => ['toto']];
        $taggedData3 = ['sheet_data' => ['tata'], 'participant_data' => ['titi']];

        $router = $this->prophesize(RouterInterface::class);
        $templating = $this->prophesize(TemplatingAdapterInterface::class);
        $taggedDataFactory = $this->prophesize(TaggedDataFactory::class);
        $sheetInfosHelper = $this->prophesize(SheetInfosHelper::class);

        $taggedDataFactory->buildTaggedDataViewForPrint($sheet1->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn($templateData1->reveal())
        ;
        $taggedDataFactory->buildTaggedDataViewForPrint($sheet2->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn($templateData2->reveal())
        ;
        $taggedDataFactory->buildTaggedDataViewForPrint($sheet3->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn($templateData3->reveal())
        ;

        $sheetInfosHelper->getInfos($sheet1->reveal(), $user1->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn([$nomenclatures1, $participants1, $taggedData1])
        ;

        $sheetInfosHelper->getInfos($sheet2->reveal(), $user2->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn([$nomenclatures2, $participants2, $taggedData2])
        ;

        $sheetInfosHelper->getInfos($sheet3->reveal(), $user4->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn([$nomenclatures3, $participants3, $taggedData3])
        ;

        $templating
            ->render(GenerateHtml::SHEET_TEMPLATE, [
                'event'         => $event->reveal(),
                'sheet'         => $sheet1->reveal(),
                'taggedData'    => $taggedData1,
                'locale'        => $locale,
                'nomenclatures' => $nomenclatures1,
                'participants'  => $participants1,
                'templateData'  => $templateData1->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn('ficheA')
        ;

        $templating
            ->render(GenerateHtml::SHEET_TEMPLATE, [
                'event'         => $event->reveal(),
                'sheet'         => $sheet2->reveal(),
                'taggedData'    => $taggedData2,
                'locale'        => $locale,
                'nomenclatures' => $nomenclatures2,
                'participants'  => $participants2,
                'templateData'  => $templateData2->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn('ficheB')
        ;

        $templating
            ->render(GenerateHtml::SHEET_TEMPLATE, [
                'event'         => $event->reveal(),
                'sheet'         => $sheet3->reveal(),
                'taggedData'    => $taggedData3,
                'locale'        => $locale,
                'nomenclatures' => $nomenclatures3,
                'participants'  => $participants3,
                'templateData'  => $templateData3->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn('ficheC')
        ;

        $templating
            ->render(GenerateHtml::PDF_TEMPLATE, [
                'event' => $event->reveal(),
                'print' => 'ficheAficheBficheC',
                'locale' => 'fr',
            ])->shouldBeCalled()
            ->willReturn('print')
        ;

        $generateHtml = new GenerateHtml(
            $router->reveal(),
            $templating->reveal(),
            $taggedDataFactory->reveal(),
            $sheetInfosHelper->reveal()
        );
        $generateHtml->printSheets($event->reveal(), $sheets, 'fr');
    }
}
