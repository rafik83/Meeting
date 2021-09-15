<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\IntlInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetRegistrationInfoQuery;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetRegistrationInfoQueryHandler;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\BooleanObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\Country;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class SheetRegistrationInfoQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $object1 = $this->prophesize(Gender::class);
        $object2 = $this->prophesize(EditableText::class);
        $object3 = $this->prophesize(Nomenclature::class);
        $object4 = $this->prophesize(Country::class);
        $object5 = $this->prophesize(BooleanObject::class);
        $object6 = $this->prophesize(EditableText::class);

        $object1->getKey()->willReturn('wxcvbn456');
        $object2->getKey()->willReturn('123azerty');
        $object3->getKey()->willReturn('ytreza321');
        $object4->getKey()->willReturn('yuiop987');
        $object5->getKey()->willReturn('789yuiop');
        $object6->getKey()->shouldNotBeCalled();

        $object1->hasTag(Tag::SHEET_DATA)->willReturn(true);
        $object2->hasTag(Tag::SHEET_DATA)->willReturn(true);
        $object3->hasTag(Tag::SHEET_DATA)->willReturn(true);
        $object4->hasTag(Tag::SHEET_DATA)->willReturn(true);
        $object5->hasTag(Tag::SHEET_DATA)->willReturn(true);
        $object6->hasTag(Tag::SHEET_DATA)->willReturn(false);

        $object1->getExportableFieldname('fr', 'de')->willReturn('Genre');
        $object2->getExportableFieldname('fr', 'de')->willReturn('Titre de fiche');
        $object3->getExportableFieldname('fr', 'de')->willReturn('Nomenclature');
        $object4->getExportableFieldname('fr', 'de')->willReturn('Pays');
        $object5->getExportableFieldname('fr', 'de')->willReturn('Nomenclature');
        $object1->getExportableContent([], 'fr')->willReturn(Gender::WOMAN);
        $object2->getExportableContent([], 'fr')->willReturn('Aanera');
        $object3->getExportableContent([], 'fr')->willReturn('Boulon rose');
        $object4->getExportableContent([], 'fr')->willReturn('France');
        $object5->getExportableContent([], 'fr')->willReturn(true);

        $templateData = $this->prophesize(TemplateData::class);
        $templateData
            ->getExportableObjects()
            ->willReturn([
                $object1->reveal(),
                $object2->reveal(),
                $object3->reveal(),
                $object4->reveal(),
                $object5->reveal(),
                $object6->reveal(),
            ])
        ;

        $translator = $this->prophesize(TranslatorInterface::class);

        $translator->trans('gender.woman', [], 'exports', 'fr')->shouldBeCalled()->willReturn('woman');
        $translator->trans('boolean.yes', [], 'exports', 'fr')->shouldBeCalled()->willReturn('yes');

        $query = new SheetRegistrationInfoQuery($templateData->reveal(), 'fr', 'de');

        $handler = new SheetRegistrationInfoQueryHandler($translator->reveal());
        $result = $handler->handle($query);

        $expected = [
            'wxcvbn456' => 'woman',
            '123azerty' => 'Aanera',
            'ytreza321' => 'Boulon rose',
            'yuiop987' => 'France',
            '789yuiop' => 'yes',
        ];

        $sheetRegistrationFields = [
            'wxcvbn456' => 'Genre',
            '123azerty' => 'Titre de fiche',
            'ytreza321' => 'Nomenclature',
            'yuiop987'  => 'Pays',
            '789yuiop'  => 'Nomenclature',
        ];

        $this->assertEquals($expected, $result);
        $this->assertEquals($sheetRegistrationFields, $handler->getSheetRegistrationFields());
    }
}
