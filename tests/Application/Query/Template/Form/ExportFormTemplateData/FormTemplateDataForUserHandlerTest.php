<?php

namespace Proximum\Vimeet\Tests\Application\Query\Template\Form\ExportFormTemplateData;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData\FormTemplateDataForUser;
use Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData\FormTemplateDataForUserHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQueryHandler;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserDataView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\User\Sheet\FirstParticipantSheetOfUserGetter;

class FormTemplateDataForUserHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $user,
        $event,
        $formTemplate,
        $formTemplateDataQueryHandler,
        $sheetRepository,
        $participantInfoGuesser,
        $sheetInfoGuesser,
        $firstParticipantSheetOfUserGetter
    ;

    public function setUp()
    {
        $this->user = $this->prophesize(User::class);
        $this->event = $this->prophesize(Event::class);
        $this->formTemplate = $this->prophesize(FormTemplate::class);

        $this->formTemplateDataQueryHandler = $this->prophesize(FormTemplateDataQueryHandler::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $this->firstParticipantSheetOfUserGetter = $this->prophesize(FirstParticipantSheetOfUserGetter::class);
    }
    public function testHandleNoSheet(): void
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn(null);
        $sheets = [$sheet->reveal()];

        $this->sheetRepository->getSheetsByUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $this->firstParticipantSheetOfUserGetter->getFirstParticipantSheet($this->user->reveal(), $sheets)
            ->shouldBeCalled()
            ->willReturn($sheet->reveal())
        ;

        $handler = new FormTemplateDataForUserHandler(
            $this->formTemplateDataQueryHandler->reveal(),
            $this->sheetRepository->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->sheetInfoGuesser->reveal(),
            $this->firstParticipantSheetOfUserGetter->reveal()
        );
        $result = $handler->handle(
            new FormTemplateDataForUser($this->event->reveal(), $this->user->reveal(), $this->formTemplate->reveal(), 'fr')
        );

        $this->assertNull($result);
    }

    public function testHandle(): void
    {
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheets = [$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()];
        $participant2 = $this->prophesize(Participant::class);
        $sheet3->getUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn($participant2);

        $this->sheetRepository->getSheetsByUserAndEvent($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $this->firstParticipantSheetOfUserGetter->getFirstParticipantSheet($this->user->reveal(), $sheets)
            ->shouldBeCalled()
            ->willReturn($sheet3->reveal())
        ;

        $this->user->getId()->shouldBeCalled()->willReturn(1);
        $this->user->getEmail()->shouldBeCalled()->willReturn('nicolas@example.net');

        $this->participantInfoGuesser->guessParticipantInfos($participant2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    Tag::PARTICIPANT_FIRSTNAME => 'Nicolas',
                    Tag::PARTICIPANT_LASTNAME => 'Example',
                    Tag::PARTICIPANT_PHONE => '+33123456789',
                ]
            )
        ;

        $this->sheetInfoGuesser->guessSheetInfos($sheet3->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                Tag::SHEET_ADDRESS => '2 boulevard des trucs',
                Tag::SHEET_CITY => 'Paris',
                Tag::SHEET_ZIPCODE => '75000',
                Tag::SHEET_COUNTRY => 'FR',
            ])
        ;

        $sheet3->getTypeTitle('fr')->shouldBeCalled()->willReturn('Exposant');
        $sheet1->getTypeTitle('fr')->shouldNotBeCalled();
        $sheet2->getTypeTitle('fr')->shouldNotBeCalled();

        $sheet3->getId()->shouldBeCalled()->willReturn(11);
        $sheet1->getId()->shouldNotBeCalled();
        $sheet2->getId()->shouldNotBeCalled();

        $sheet3->getTitle()->shouldBeCalled()->willReturn('Truc Muche');
        $sheet1->getTitle()->shouldNotBeCalled();
        $sheet2->getTitle()->shouldNotBeCalled();

        $sheet3->getCategoriesTitles('fr')->shouldBeCalled()->willReturn('Exposants');
        $sheet1->getCategoriesTitles('fr')->shouldNotBeCalled();
        $sheet2->getCategoriesTitles('fr')->shouldNotBeCalled();

        $templateData = $this->prophesize(TemplateData::class);

        $object1 = $this->prophesize(EditableText::class);
        $object2 = $this->prophesize(EditableText::class);
        $object3 = $this->prophesize(Nomenclature::class);
        $object4 = $this->prophesize(EditableText::class);

        $object1->getKey()->shouldBeCalled()->willReturn('key123');
        $object2->getKey()->shouldBeCalled()->willReturn('key1234');
        $object3->getKey()->shouldBeCalled()->willReturn('key12345');
        $object4->getKey()->shouldBeCalled()->willReturn('key123456');

        $object1->getExportableContent()->shouldBeCalled()->willReturn('Test');
        $object2->getExportableContent()->shouldBeCalled()->willReturn('Bidule');
        $object3->getExportableContent()->shouldBeCalled()->willReturn('Lorem > Ipsum');
        $object4->getExportableContent()->shouldBeCalled()->willReturn('10/05/2018');

        $templateData
            ->getExportableObjects()
            ->shouldBeCalled()
            ->willReturn([
                $object1,
                $object2,
                $object3,
                $object4,
            ])
        ;

        $this->formTemplateDataQueryHandler
            ->handle(new FormTemplateDataQuery($this->formTemplate->reveal(), $sheet3->reveal(), $participant2->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $handler = new FormTemplateDataForUserHandler(
            $this->formTemplateDataQueryHandler->reveal(),
            $this->sheetRepository->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->sheetInfoGuesser->reveal(),
            $this->firstParticipantSheetOfUserGetter->reveal()
        );
        $result = $handler->handle(
            new FormTemplateDataForUser($this->event->reveal(), $this->user->reveal(), $this->formTemplate->reveal(), 'fr')
        );

        $expected = new UserDataView(
            1,
            'nicolas@example.net',
            'Nicolas',
            'Example',
            '+33123456789',
            '',
            11,
            'Truc Muche',
            'Exposant',
            'Exposants',
            '2 boulevard des trucs',
            '75000',
            'Paris',
            'FR',
            [
                'key123' => 'Test',
                'key1234' => 'Bidule',
                'key12345' => 'Lorem > Ipsum',
                'key123456' => '10/05/2018',
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
