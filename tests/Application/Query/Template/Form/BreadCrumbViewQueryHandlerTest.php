<?php

namespace Proximum\Vimeet\Tests\Application\Query\Template\Form;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Template\Form\BreadCrumbViewQuery;
use Proximum\Vimeet\Application\Query\Template\Form\BreadCrumbViewQueryHandler;
use Proximum\Vimeet\Application\View\Template\Form\BreadCrumbView;
use Proximum\Vimeet\Application\View\Template\Form\StepView;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;

class BreadCrumbViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $templateData = $this->prophesize(TemplateData::class);
        $query = new BreadCrumbViewQuery($templateData->reveal(), 2, 'fr');

        $block1 = $this->prophesize(Block::class);
        $block2 = $this->prophesize(Block::class);
        $block3 = $this->prophesize(Block::class);
        $block4 = $this->prophesize(Block::class);
        $block1->getTitle('fr')->shouldBeCalled()->willReturn('Title 1');
        $block2->getTitle('fr')->shouldBeCalled()->willReturn('Title 2');
        $block3->getTitle('fr')->shouldBeCalled()->willReturn('Title 3');
        $block4->getTitle('fr')->shouldBeCalled()->willReturn('Title 4');

        $object1B1 = $this->prophesize(EditableText::class);
        $object1B2 = $this->prophesize(EditableText::class);
        $object2B2 = $this->prophesize(EditableText::class);
        $object1B3 = $this->prophesize(EditableText::class);
        $object2B3 = $this->prophesize(EditableText::class);

        $blocks = [
            1 => $block1->reveal(),
            2 => $block2->reveal(),
            3 => $block3->reveal(),
            4 => $block4->reveal(),
        ];
        $templateData->getBlocksAsSteps()->shouldBeCalled()->willReturn($blocks);

        $block1->getEditableObjects()->shouldBeCalled()->willReturn([$object1B1->reveal()]);
        $block2->getEditableObjects()->shouldBeCalled()->willReturn([$object1B2->reveal(), $object2B2->reveal()]);
        $block3->getEditableObjects()->shouldBeCalled()->willReturn([$object1B3->reveal(), $object2B3->reveal()]);
        $block4->getEditableObjects()->shouldNotBeCalled();

        $object1B1->getRequired()->shouldBeCalled()->willReturn(true);
        $object1B1->isEmpty()->shouldBeCalled()->willReturn(false);

        $object1B2->getRequired()->shouldBeCalled()->willReturn(false);
        $object1B2->isEmpty()->shouldNotBeCalled();

        $object2B2->getRequired()->shouldBeCalled()->willReturn(true);
        $object2B2->isEmpty()->shouldBeCalled()->willReturn(false);

        $object1B3->getRequired()->shouldBeCalled()->willReturn(true);
        $object1B3->isEmpty()->shouldBeCalled()->willReturn(true);

        $object2B3->getRequired()->shouldNotBeCalled();
        $object2B3->isEmpty()->shouldNotBeCalled();

        $handler = new BreadCrumbViewQueryHandler();
        $result = $handler->handle($query);

        $steps = [
            1 => new StepView(1, 'Title 1', true),
            2 => new StepView(2, 'Title 2', true),
            3 => new StepView(3, 'Title 3', true),
            4 => new StepView(4, 'Title 4', false),
        ];
        $expected = new BreadCrumbView($steps, 2);
        $this->assertEquals($expected, $result);
    }
}
