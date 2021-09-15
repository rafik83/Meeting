<?php

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Template\PrintTemplateResolver;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TrackingUrlTransformer;
use Proximum\Vimeet\Domain\View\Template\TaggedDataView;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class TaggedDataFactoryTest extends TestCase
{
    public function testHandle()
    {
        $sheet    = SheetFactory::create();
        $locale   = 'fr';
        $template = [
            '0aea62b2' => [
                'component' => 'object',
                'type'      => 'editable-text',
                'config'    => [
                    'translatable' => true,
                    'tags'         => [Tag::SHEET_TITLE, Tag::SHEET_DATA], // registration template tags
                    'tag'          => Tag::SHEET_TITLE, // sheet template tag
                ],
            ],
            '0aea62b3' => [
                'component' => 'object',
                'type'      => 'editable-text',
                'config'    => [
                    'translatable' => false,
                    'tags'         => ['sheet_generic_tag_1', Tag::SHEET_DATA], // registration template tags
                    'tag'          => 'sheet_generic_tag_1', // sheet template tag
                ],
            ],
        ];

        $data = [
            '0aea62b2' => [
                'text' => ['fr' => 'Lorem ipsum ec74be5e fr', 'en' => 'Lorem ipsum ec74be5e en'],
            ],
            '0aea62b3' => [
                'text' => ['fr' => 'Lorem ipsum ec74be5e fr'],
            ],
        ];

        $nomenclatureRepository = $this->prophesize(NomenclatureRepositoryInterface::class);

        $event = $this->prophesize(Event::class);
        $factory           = new TemplateDataFactory($nomenclatureRepository->reveal());
        $templateData      = $factory->create($template, $data, 'fr', 'fr', $event->reveal());
        $sheetTemplateData = $factory->create($template, $data, 'fr', 'fr', $event->reveal());

        // Expected
        $expectedTaggedDataViewObject1 = new TaggedDataView(
            'editable-text',
            true, // translatable
            ['fr' => 'Lorem ipsum ec74be5e fr', 'en' => 'Lorem ipsum ec74be5e en'],
            'Lorem ipsum ec74be5e fr',
            Tag::SHEET_TITLE,
            false,
            null,
            '0aea62b2'
        );
        $expectedTaggedDataViewObject2 = new TaggedDataView(
            'editable-text',
            false, // translatable
            [],
            'Lorem ipsum ec74be5e fr',
            'sheet_generic_tag_1',
            false,
            null,
            '0aea62b3'
        );

        // Mock
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $applyer = $this->prophesize(Applyer::class);
        $trackingUrlTransformer = $this->prophesize(TrackingUrlTransformer::class);

        $templateDataFactory
            ->createRegistrationFromSheet($sheet, $locale)
            ->shouldBeCalled()
            ->willReturn($templateData);

        /* @var TemplateData $sheetTemplateData */
        $templateDataFactory
            ->createFromSheet($sheet, $locale)
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData);

        $printTemplateResolver = $this->prophesize(PrintTemplateResolver::class);

        $taggedDataFactory = new TaggedDataFactory(
            $templateDataFactory->reveal(),
            $printTemplateResolver->reveal(),
            $applyer->reveal(),
            $trackingUrlTransformer->reveal()
        );

        $sheetTemplateData = $taggedDataFactory->buildTaggedDataView($sheet, $locale);

        $objectEditableText1 = $sheetTemplateData->getObject('0aea62b2');
        $objectEditableText2 = $sheetTemplateData->getObject('0aea62b3');

        $this->assertCount(1, $objectEditableText1->getTaggedDataViews());
        $this->assertCount(1, $objectEditableText2->getTaggedDataViews());
        $this->assertEquals($expectedTaggedDataViewObject1, $objectEditableText1->getTaggedDataViews()[0]);
        $this->assertEquals($expectedTaggedDataViewObject2, $objectEditableText2->getTaggedDataViews()[0]);
    }
}
