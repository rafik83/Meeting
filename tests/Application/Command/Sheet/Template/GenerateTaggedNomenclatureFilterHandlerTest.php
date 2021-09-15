<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Command\Sheet\Template\GenerateTaggedNomenclatureFilter;
use Proximum\Vimeet\Application\Command\Sheet\Template\GenerateTaggedNomenclatureFilterHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class GenerateTaggedNomenclatureFilterHandlerTest extends TestCase
{
    private $event;
    private $sheetTemplateRepository;
    private $taggedNomenclatureFilterRepository;
    private $templateDataFactory;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheetTemplateRepository = $this->prophesize(SheetTemplateRepositoryInterface::class);
        $this->taggedNomenclatureFilterRepository = $this->prophesize(TaggedNomenclatureFilterRepositoryInterface::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
    }

    public function testHandle()
    {
        $tags = Tag::getGenericSheetTemplateTags();
        $template = $this->prophesize(SheetTemplate::class);
        $templateData = $this->prophesize(TemplateData::class);
        $editableTextObject = $this->prophesize(EditableText::class);
        $nomenclatureObject = $this->prophesize(Nomenclature::class);

        foreach ($tags as $tag) {
            $nomenclatureObject->hasTag($tag)->willReturn(false);
        }

        $nomenclatureObject->hasTag('sheet_template_generic_tag_1')->willReturn(true);
        $nomenclatureObject->getNomenclatureId()->shouldBeCalled()->willReturn(1337);

        $templateData->getObjects()->shouldBeCalled()->willReturn(
            [
                $editableTextObject->reveal(),
                $nomenclatureObject->reveal(),
            ]
        )
        ;

        $this
            ->sheetTemplateRepository
            ->getUsedTemplateForGivenEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$template->reveal()])
        ;
        $this
            ->templateDataFactory
            ->createFromTemplate($template->reveal())
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;

        $this
            ->taggedNomenclatureFilterRepository
            ->deleteForEventAndTags($this->event->reveal(), $tags)
            ->shouldBeCalled()
        ;

        $this
            ->taggedNomenclatureFilterRepository->add(
                new TaggedNomenclatureFilter($this->event->reveal(), 'sheet_template_generic_tag_1', [1337])
            )
            ->shouldBeCalled()
        ;

        $generateTaggedNomenclatureFilterHandler = new GenerateTaggedNomenclatureFilterHandler(
            $this->sheetTemplateRepository->reveal(),
            $this->taggedNomenclatureFilterRepository->reveal(),
            $this->templateDataFactory->reveal()
        );
        $generateTaggedNomenclatureFilterHandler->handle(new GenerateTaggedNomenclatureFilter($this->event->reveal()));
    }
}
