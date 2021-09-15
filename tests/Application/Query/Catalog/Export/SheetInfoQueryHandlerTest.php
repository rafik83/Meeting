<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetInfoQuery;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetInfoQueryHandler;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\TemplateObject\TagsCollection;

class SheetInfoQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $taggedData = [
            Tag::SHEET_TITLE => [
                'Aanera',
            ],
        ];
        $locale = 'fr';
        $fallback = 'de';

        $object1 = $this->prophesize(TagsCollection::class);
        $object2 = $this->prophesize(EditableText::class);
        $object3 = $this->prophesize(Nomenclature::class);

        $object1->getKey()->shouldNotBeCalled();

        $object2->getKey()->willReturn('123azerty');
        $object3->getKey()->willReturn('ytreza321');
        $object2->getExportableFieldname('fr', 'de')->willReturn('Titre de fiche');
        $object3->getExportableFieldname('fr', 'de')->willReturn('Nomenclature');
        $object2->getExportableContent($taggedData, 'fr')->willReturn('Aanera');
        $object3->getExportableContent($taggedData, 'fr')->willReturn('Boulon rose');

        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getExportableObjects()->willReturn([$object1->reveal(), $object2->reveal(), $object3->reveal()]);

        $query = new SheetInfoQuery(
            $templateData->reveal(),
            $taggedData,
            $locale,
            $fallback
        );
        $handler = new SheetInfoQueryHandler();

        $result = $handler->handle($query);

        $expected = [
            '123azerty' => 'Aanera',
            'ytreza321' => 'Boulon rose',
        ];

        $sheetFields = [
            '123azerty' => 'Titre de fiche',
            'ytreza321' => 'Nomenclature',
        ];

        $this->assertEquals($expected, $result);
        $this->assertEquals($sheetFields, $handler->getSheetFields());
    }
}
