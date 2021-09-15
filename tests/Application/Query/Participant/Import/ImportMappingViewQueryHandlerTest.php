<?php

namespace Proximum\Vimeet\Tests\Application\Query\Participant\Import;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Query\Participant\Import\ImportMappingViewQuery;
use Proximum\Vimeet\Application\Query\Participant\Import\ImportMappingViewQueryHandler;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Sheet\ImportMappingRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Country;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Infrastructure\Adapter\SessionAdapter;

class ImportMappingViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $type = $this->prophesize(Type::class);

        $importMappingRepository = $this->prophesize(ImportMappingRepositoryInterface::class);
        $serializerAdapter = $this->prophesize(SerializerAdapterInterface::class);
        $session = $this->prophesize(SessionAdapter::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $translator = $this->prophesize(TranslatorInterface::class);

        $session
            ->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE)
            ->shouldBeCalled()
            ->willReturn(tempnam(sys_get_temp_dir(), ''))
        ;

        $session->get(ParticipantImportTag::PARTICIPANT_IMPORT_ALLOW_MULTI_SHEET)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $data = [
            0 => [
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'email' => 'email',
                'group_title' => 'group_title',
                'position' => 'position',
                'staff' => 'staff',
                'country' => 'country',
            ],
            1 => [
                'Jean',
                'Dupont',
                'jean.dupont@example.net',
                'Group',
                'Director',
                '10 - 25',
                'France',
            ]
        ];

        $serializerAdapter
            ->decode(Argument::any(), 'csv', ['csv_delimiter' => ';'])
            ->shouldBeCalled()
            ->willReturn($data)
        ;

        $registrationTemplate = $this->prophesize(TemplateData::class);
        $sheetTemplate = $this->prophesize(TemplateData::class);

        $templateDataFactory->createSheetTemplateFromType($type->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($sheetTemplate->reveal())
        ;

        $templateDataFactory->createRegistrationFromType($type->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($registrationTemplate->reveal())
        ;

        $object1 = $this->prophesize(EditableText::class);
        $object2 = $this->prophesize(EditableText::class);
        $object3 = $this->prophesize(Nomenclature::class);
        $object4 = $this->prophesize(Country::class);

        $object5 = $this->prophesize(EditableText::class);
        $object6 = $this->prophesize(Nomenclature::class);
        $object7 = $this->prophesize(EditableText::class);

        $object1->getLabel('fr')->shouldBeCalled()->willReturn('object1');
        $object2->getLabel('fr')->shouldBeCalled()->willReturn('object2');
        $object3->getLabel('fr')->shouldBeCalled()->willReturn('object3');
        $object4->getLabel('fr')->shouldBeCalled()->willReturn('object4');
        $object5->getLabel('fr')->shouldBeCalled()->willReturn('object5');
        $object6->getLabel('fr')->shouldBeCalled()->willReturn('object6');
        $object7->getLabel('fr')->shouldBeCalled()->willReturn('object7');

        $object1->getKey()->shouldBeCalled()->willReturn('key1');
        $object2->getKey()->shouldBeCalled()->willReturn('key2');
        $object3->getKey()->shouldBeCalled()->willReturn('key3');
        $object4->getKey()->shouldBeCalled()->willReturn('key4');
        $object5->getKey()->shouldBeCalled()->willReturn('key5');
        $object6->getKey()->shouldBeCalled()->willReturn('key6');
        $object7->getKey()->shouldBeCalled()->willReturn('key7');

        $registrationTemplate
            ->getParticipantAndSheetDataExceptedImageObject()
            ->shouldBeCalled()
            ->willReturn([
                $object1->reveal(),
                $object2->reveal(),
                $object3->reveal(),
                $object4->reveal(),
            ])
        ;

        $sheetTemplate
            ->getEditableTextAndNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([
                $object5->reveal(),
                $object6->reveal(),
                $object7->reveal(),
            ])
        ;

        $translator->trans('admin.participant_import.header.registration', [], 'messages', 'fr')
            ->shouldBeCalled()
            ->willReturn('Inscription : ')
        ;

        $translator->trans('admin.participant_import.header.sheet', [], 'messages', 'fr')
            ->shouldBeCalled()
            ->willReturn('Fiche : ')
        ;

        $session->get(ParticipantImportTag::PARTICIPANT_IMPORT_SAVED_MAPPING)
            ->shouldBeCalled()
            ->willReturn(12)
        ;

        $importMappingRepository->getById(12)->shouldBeCalled()->willReturn(null);

        $handler = new ImportMappingViewQueryHandler(
            $importMappingRepository->reveal(),
            $serializerAdapter->reveal(),
            $session->reveal(),
            $templateDataFactory->reveal(),
            $translator->reveal()
        );

        $query = new ImportMappingViewQuery($type->reveal(), 'fr');
        $result = $handler->handle($query);

        $fieldHeaders = [
            'firstName',
            'lastName',
            'email',
            'group_title',
            'position',
            'staff',
            'country',
        ];
        $headers = [
            ParticipantImportTag::REGISTRATION_FIELD_IGNORE => 'form.participant_import.field.ignore',
            ParticipantImportTag::REGISTRATION_FIELD_MAIL => 'form.participant_import.field.mail',
            ParticipantImportTag::REGISTRATION_FIELD_LOCALE => 'form.participant_import.field.locale',
            ParticipantImportTag::FIELD_GROUP_TITLE => 'form.participant_import.field.group_title',
            'key1' => 'Inscription : object1',
            'key2' => 'Inscription : object2',
            'key3' => 'Inscription : object3',
            'key4' => 'Inscription : object4',
            'key5' => 'Fiche : object5',
            'key6' => 'Fiche : object6',
            'key7' => 'Fiche : object7',

        ];
        $expected = new ImportMappingView($fieldHeaders, $headers, true, null);

        $this->assertEquals($expected, $result);
    }
}
