<?php

namespace Proximum\Vimeet\Tests\Application\Query\Template\Form;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Application\Query\Template\Form\BreadCrumbViewQuery;
use Proximum\Vimeet\Application\Query\Template\Form\BreadCrumbViewQueryHandler;
use Proximum\Vimeet\Application\Query\Template\Form\FillStepQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FillStepQueryHandler;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQueryHandler;
use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Application\View\Template\Form\BreadCrumbView;
use Proximum\Vimeet\Application\View\Template\Form\StepView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Exception\BlockForGivenStepNotFoundException;
use Proximum\Vimeet\Domain\Template\Exception\GivenStepIsRequiredAndNotFilledException;
use Proximum\Vimeet\Domain\Template\TemplateData;

class FillStepQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $formTemplate, $sheet, $participant, $formTemplateDataQueryHandler, $markdown, $breadCrumbViewQueryHandler;

    public function setup()
    {
        $this->formTemplate = $this->prophesize(FormTemplate::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->formTemplateDataQueryHandler = $this->prophesize(FormTemplateDataQueryHandler::class);
        $this->markdown = $this->prophesize(MarkdownAdapterInterface::class);
        $this->breadCrumbViewQueryHandler = $this->prophesize(BreadCrumbViewQueryHandler::class);
    }

    public function testHandleBlockNotFound()
    {
        $this->expectException(BlockForGivenStepNotFoundException::class);

        $templateData = $this->prophesize(TemplateData::class);
        $this->formTemplateDataQueryHandler
            ->handle(new FormTemplateDataQuery($this->formTemplate->reveal(), $this->sheet->reveal(), $this->participant->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $templateData->getBlock(12)->shouldBeCalled()->willReturn(null);

        $query = new FillStepQuery(
            $this->formTemplate->reveal(),
            $this->sheet->reveal(),
            $this->participant->reveal(),
            'fr',
            12
        );
        $handler = new FillStepQueryHandler(
            $this->formTemplateDataQueryHandler->reveal(),
            $this->markdown->reveal(),
            $this->breadCrumbViewQueryHandler->reveal()
        );
        $handler->handle($query);
    }

    public function testHandleWithRequiredStepNotFilled()
    {
        $this->expectException(GivenStepIsRequiredAndNotFilledException::class);

        $templateData = $this->prophesize(TemplateData::class);
        $this->formTemplateDataQueryHandler
            ->handle(new FormTemplateDataQuery($this->formTemplate->reveal(), $this->sheet->reveal(), $this->participant->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $block = $this->prophesize(Block::class);
        $templateData->getBlock(3)->shouldBeCalled()->willReturn($block);

        $stepView1 = new StepView(1, 'Title 1', true);
        $stepView2 = new StepView(2, 'Title 2', true);
        $stepView3 = new StepView(3, 'Title 3', false);
        $stepView4 = new StepView(4, 'Title 4', false);
        $breadCrumb = new BreadCrumbView(
            [$stepView1, $stepView2, $stepView3, $stepView4],
            3
        );

        $this->breadCrumbViewQueryHandler
            ->handle(new BreadCrumbViewQuery($templateData->reveal(), 3, 'fr'))
            ->shouldBeCalled()
            ->willReturn($breadCrumb)
        ;

        $query = new FillStepQuery(
            $this->formTemplate->reveal(),
            $this->sheet->reveal(),
            $this->participant->reveal(),
            'fr',
            3
        );
        $handler = new FillStepQueryHandler(
            $this->formTemplateDataQueryHandler->reveal(),
            $this->markdown->reveal(),
            $this->breadCrumbViewQueryHandler->reveal()
        );
        $handler->handle($query);
    }

    public function testHandle()
    {
        $templateData = $this->prophesize(TemplateData::class);
        $this->formTemplateDataQueryHandler
            ->handle(new FormTemplateDataQuery($this->formTemplate->reveal(), $this->sheet->reveal(), $this->participant->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $block = $this->prophesize(Block::class);
        $templateData->getBlock(3)->shouldBeCalled()->willReturn($block);
        $block->getDescription('fr')->shouldBeCalled()->willReturn('Ceci est une description en markdown');

        $this->markdown->toHtml('Ceci est une description en markdown')->shouldBeCalled()->willReturn('Ceci est une description');

        $stepView1 = new StepView(1, 'Title 1', true);
        $stepView2 = new StepView(2, 'Title 2', true);
        $stepView3 = new StepView(3, 'Title 3', true);
        $stepView4 = new StepView(4, 'Title 4', false);
        $breadCrumb = new BreadCrumbView(
            [
                1 => $stepView1,
                2 => $stepView2,
                3 => $stepView3,
                4 => $stepView4,
            ],
            3
        );

        $this->breadCrumbViewQueryHandler
            ->handle(new BreadCrumbViewQuery($templateData->reveal(), 3, 'fr'))
            ->shouldBeCalled()
            ->willReturn($breadCrumb)
        ;

        $query = new FillStepQuery(
            $this->formTemplate->reveal(),
            $this->sheet->reveal(),
            $this->participant->reveal(),
            'fr',
            3
        );
        $handler = new FillStepQueryHandler(
            $this->formTemplateDataQueryHandler->reveal(),
            $this->markdown->reveal(),
            $this->breadCrumbViewQueryHandler->reveal()
        );
        $result = $handler->handle($query);

        $view = new BlockStepView(
            $block->reveal(),
            'Ceci est une description',
            $breadCrumb
        );

        $this->assertEquals($view, $result);
    }
}
