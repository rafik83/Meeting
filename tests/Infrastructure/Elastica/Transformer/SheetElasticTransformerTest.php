<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Elastica\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Elastica\Document;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Nomenclature as NomenclatureModel;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Order\SheetOrderStatus;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Availability\ConfirmationStatus;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;
use Proximum\Vimeet\Domain\Sheet\Phone\ValidationStatus;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\View\Template\TaggedDataView;
use Proximum\Vimeet\Infrastructure\Elastica\Transformer\SheetElasticTransformer;

class SheetElasticTransformerTest extends TestCase
{
    public function testTransform()
    {
        $category1 = $this->prophesize(Category::class);
        $category1->getId()->willReturn(999);

        $category2 = $this->prophesize(Category::class);
        $category2->getId()->willReturn(888);

        $type = $this->prophesize(Type::class);
        $type->getId()->willReturn(1337);
        $type->getCategories()->willReturn(
            new ArrayCollection(
                [
                    $category1->reveal(),
                    $category2->reveal(),
                ]
            )
        );

        $account = $this->prophesize(User\Account::class);
        $account->getLastName()->willReturn('Scarface');

        $owner = $this->prophesize(User::class);
        $owner->getId()->willReturn(13);
        $owner->getEmail()->willReturn('owner@email.com');
        $owner->getAccount()->willReturn($account->reveal());

        $user1 = $this->prophesize(User::class);
        $user1->getId()->willReturn(42);
        $user1->getEmail()->willReturn('user1@email.com');

        $user2 = $this->prophesize(User::class);
        $user2->getEmail()->willReturn('user2@email.com');

        $spot = $this->prophesize(Spot::class);
        $spot->getReference()->willReturn('A21');
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(66);
        $event->getLocales()->willReturn(['fr', 'en']);
        $event->getFallback()->willReturn('fr');

        $meetingSlot1 = $this->prophesize(MeetingSlot::class);
        $meetingSlot1->getId()->willReturn(11);
        $availableSlot1 = $this->prophesize(Sheet\AvailableSlot::class);
        $availableSlot1->getSlot()->willReturn($meetingSlot1->reveal());

        $meetingSlot2 = $this->prophesize(MeetingSlot::class);
        $meetingSlot2->getId()->willReturn(12);
        $availableSlot2 = $this->prophesize(Sheet\AvailableSlot::class);
        $availableSlot2->getSlot()->willReturn($meetingSlot2->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getUser()->willReturn($user1);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getUser()->willReturn($user2);

        $follower = $this->prophesize(Admin::class);
        $follower->getId()->willReturn(777);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $sheet->getType()->willReturn($type->reveal());
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getParticipantsArray()->willReturn(
            [
                $participant1->reveal(),
                $participant2->reveal(),
            ]
        )
        ;
        $sheet->getOwner()->willReturn($owner->reveal());
        $sheet->hasUserParticipant($owner->reveal())->willReturn(false);
        $sheet->getState()->willReturn(Sheet::STATE_ACCEPTED);
        $sheet->getValidationState()->willReturn(Sheet::STATE_VALIDATION_PENDING);
        $sheet->getAgendaConfirmedStatus()->willReturn(Sheet::AGENDA_ALL_CONFIRMED);
        $sheet->getPhoneValidationStatus()->willReturn(ValidationStatus::NOT_CONCERNED);
        $sheet->getAvailabilityConfirmationStatus()->willReturn(ConfirmationStatus::NONE_CONFIRMED);
        $sheet->isEnabled()->willReturn(true);
        $sheet->isCompleted()->willReturn(false);
        $sheet->getCompleteness()->willReturn(88);
        $sheet->getFollower()->willReturn($follower->reveal());
        $sheet->getCommercialStatus()->willReturn(CommercialStatus::STATUS_INTEREST);
        $sheet->countParticipants()->willReturn(2);
        $sheet->isImported()->willReturn(false);
        $sheet->isInInternalCatalog()->willReturn(true);
        $sheet->isInExternalOrInternalCatalog()->willReturn(true);
        $sheet->attend()->willReturn(true);
        $sheet->hasGroup()->willReturn(true);
        $sheet->hasSpot()->willReturn(true);
        $sheet->getSpot()->willReturn($spot->reveal());
        $sheet->getAvailableSlots()->willReturn(
            [
                $availableSlot1->reveal(),
                $availableSlot2->reveal(),
            ]
        )
        ;
        $sheet->getLastLoginAt()->willReturn(new \DateTime('2016-06-18 20:45:00'));
        $sheet->getCreatedAt()->willReturn(new \DateTime('2016-05-11 09:13:13'));
        $sheet->getInCatalogAt()->willReturn(new \DateTime('2016-08-12 01:23:33'));
        $sheet->getReminderDate()->willReturn(new \DateTime('2016-06-18 09:30:00'));

        $registrationEditableText = new EditableText(
            'registration-editable-text-1',
            'editable-text',
            [
                'translatable' => false,
                'tags' => ['sheet_generic_tag_1', 'sheet_data']
            ],
            'fr',
            'fr'
        );
        $registrationEditableText->setContentValue('Registration whatever text');

        $registrationNomenclature = new Nomenclature(
            'registration-nomenclature-1',
            'nomenclature',
            [
                'translatable' => false,
                'tags' => ['sheet_generic_tag_2', 'sheet_data']
            ],
            'fr',
            'fr'
        );
        $registrationNomenclature->setItems(['item1', 'item3']);

        $registrationBlock = new Block('12', [], 'fr', 'fr');
        $registrationBlock->addChild(1, '69b3cde3', $registrationEditableText);
        $registrationBlock->addChild(1, 'c67abcd9', $registrationNomenclature);
        $registrationTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $registrationTemplateData->addChild(0, '811f6edf', $registrationBlock);

        $editableTextSheetFr = new EditableText(
            '69b3cde3',
            'editable-text',
            [
                'translatable' => true,
            ],
            'fr',
            'fr'
        );
        $editableTextSheetFr->setContentValue('Ma description');

        $nomenclatureModel = $this->prophesize(NomenclatureModel::class);
        $nomenclatureModel->getLabels('fr')->shouldBeCalled()->willReturn([]);
        $nomenclatureModel->getId()->shouldBeCalled()->willReturn(1984);
        $sheetTemplateNomenclature = new Nomenclature(
            'sheet-template-nomenclature-1',
            'nomenclature',
            [
                'translatable' => false,
                'tags' => ['sheet_template_generic_tag_1', 'sheet_data']
            ],
            'fr',
            'fr'
        );
        $sheetTemplateNomenclature->setNomenclature($nomenclatureModel->reveal());
        $sheetTemplateNomenclature->setItems(['item3', 'item5']);

        $blockFr = new Block('12', [], 'fr', 'fr');
        $blockFr->addChild(1, '69b3cde3', $editableTextSheetFr);
        $blockFr->addChild(1, 'abcd1234', $sheetTemplateNomenclature);
        $sheetTemplateWithTaggedDataFr = new TemplateData('root', [], 'fr', 'fr');
        $sheetTemplateWithTaggedDataFr->addChild(0, '811f6edf', $blockFr);
        $sheetTemplateWithTaggedDataFr->setTaggedDataViews(
            [new TaggedDataView('editable-text', false, [], 'sheet_generic_tag_1', 'Tagged data from registration', false, null, 'r@nd0m')]
        );

        $editableTextSheetEn = new EditableText(
            '69b3cde3',
            'editable-text',
            [
                'translatable' => true,
            ],
            'en',
            'fr'
        );
        $editableTextSheetEn->setContentValue('My description');
        $blockEn = new Block('12', [], 'en', 'fr');
        $blockEn->addChild(1, '69b3cde3', $editableTextSheetEn);
        $sheetTemplateWithTaggedDataEn = new TemplateData('root', [], 'en', 'fr');
        $sheetTemplateWithTaggedDataEn->addChild(0, '811f6edf', $blockEn);
        $sheetTemplateWithTaggedDataEn->setTaggedDataViews(
            [new TaggedDataView('editable-text', false, [], 'sheet_generic_tag_1', 'Tagged data from registration', false, null, 'r@nd0m')]
        );

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser->guessSheetTitle($sheet->reveal(), 'fr')->shouldBeCalled()->willReturn('Hollywood Studio');

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessParticipantLastName($participant1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('Willis')
        ;
        $participantInfoGuesser
            ->guessParticipantPosition($participant1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('Acteur')
        ;
        $participantInfoGuesser
            ->guessParticipantLastName($participant2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('Streep')
        ;
        $participantInfoGuesser
            ->guessParticipantPosition($participant2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('Actrice')
        ;

        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $cartRowRepository->findBySheet($sheet->reveal())->shouldBeCalled()->willReturn([]);

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository
            ->hasParticipationsBySheet($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->hasRequestSentBySheet($sheet->reveal())->shouldBeCalled()->willReturn(false);
        $requestRepository->hasPendingPropositionReceivedBySheet($sheet->reveal())->shouldBeCalled()->willReturn(true);

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory
            ->createRegistrationFromSheet($sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData)
        ;
        $templateDataFactory
            ->createFromSheet($sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($sheetTemplateWithTaggedDataFr)
        ;
        $templateDataFactory
            ->createFromSheet($sheet->reveal(), 'en')
            ->shouldBeCalled()
            ->willReturn($sheetTemplateWithTaggedDataEn)
        ;

        $balance = $this->prophesize(Balance::class);
        $balance->getRemainingToPay($sheet->reveal())->shouldBeCalled();

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->hasScheduledMeeting($sheet->reveal())->shouldBeCalled()->willReturn(true);

        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $invoiceRepository->hasInvoice($sheet->reveal())->shouldBeCalled()->willReturn(false);

        $campaignRepository = $this->prophesize(CampaignRepositoryInterface::class);
        $campaignRepository->getBySheet($sheet->reveal())->shouldBeCalled()->willReturn([44, 128]);

        $taggedDataFactory = $this->prophesize(TaggedDataFactory::class);
        $taggedDataFactory
            ->buildTaggedDataView($sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($sheetTemplateWithTaggedDataFr)
        ;
        $taggedDataFactory
            ->buildTaggedDataView($sheet->reveal(), 'en')
            ->shouldBeCalled()
            ->willReturn($sheetTemplateWithTaggedDataEn)
        ;

        $sheetOrderStatus = $this->prophesize(SheetOrderStatus::class);
        $sheetOrderStatus->getStatus($sheet->reveal())->shouldBeCalled()->willReturn(Sheet\Constant::NO_ORDER);

        $transformer = new SheetElasticTransformer(
            $sheetInfoGuesser->reveal(),
            $participantInfoGuesser->reveal(),
            $cartRowRepository->reveal(),
            $happeningParticipationRepository->reveal(),
            $requestRepository->reveal(),
            $templateDataFactory->reveal(),
            $taggedDataFactory->reveal(),
            $balance->reveal(),
            $meetingRepository->reveal(),
            $invoiceRepository->reveal(),
            $sheetOrderStatus->reveal(),
            $campaignRepository->reveal()
        );

        $expectedDocument = new Document(
            1,
            [
                'id' => 1,
                'sheetName' => 'Hollywood Studio',
                'state' => 'accepted',
                'validationState' => 'pending',
                'agendaConfirmedStatus' => 'all_agenda_confirmed',
                'phoneValidationStatus' => 'phone_not_concerned',
                'availabilityConfirmationStatus' => 'none_confirmed',
                'enabled' => true,
                'completed' => false,
                'completeness' => 88,
                'type' => 1337,
                'categories' => [
                    ['id' => 999],
                    ['id' => 888],
                ],
                'followUp' => 777,
                'commercialStatus' => 'interest',
                'participantNumber' => 2,
                'participants' => [
                    [
                        'email' => 'user1@email.com',
                        'lastname' => 'Willis',
                        'position' => 'Acteur',
                    ],
                    [
                        'email' => 'user2@email.com',
                        'lastname' => 'Streep',
                        'position' => 'Actrice',
                    ],
                    [
                        'email' => 'owner@email.com',
                        'lastname' => 'Scarface',
                    ],
                ],
                'event' => 66,
                'owner' => 13,
                'ownerEmail' => 'owner@email.com',
                'remainingToPay' => 0,
                'imported' => false,
                'lastLoginAt' => '2016-06-18T20:45:00+00:00',
                'createdAt' => '2016-05-11T09:13:13+00:00',
                'inCatalog' => true,
                'inCatalogAt' => '2016-08-12T01:23:33+00:00',
                'booleanFilter' => [],
                'orderStatus' => 'no_order',
                'hasCart' => false,
                'organizationCategory' => null,
                'city' => null,
                'zipcode' => null,
                'country' => [],
                'countryCode' => null,
                'nomenclatureItems' => [
                    ['key' => 'item3'],
                    ['key' => 'item5'],
                ],
                'nomenclatureItemsSupply' => [],
                'nomenclatureItemsNeeds' => [],
                'keywords' => [],
                'hasHappeningParticipation' => true,
                'hasMeetingRequest' => false,
                'hasPendingMeetingProposition' => true,
                'hasScheduledMeeting' => true,
                'hasInvoice' => false,
                'attend' => true,
                'hasGroup' => true,
                'hasSpot' => true,
                'availableSlotIds' => [
                    ['id' => 11],
                    ['id' => 12],
                ],
                'reminderDate' => '2016-06-18T09:30:00+00:00',
                'content' => 'Ma description My description',
                'content_fr' => 'Ma description',
                'content_en' => 'My description',
                'filledFilter' => [],
                'spotReference' => 'A21',
                'nestedTaggedData' => [
                    [
                        'tag' => 'sheet_generic_tag_2',
                        'values' => [
                            [
                                'tag' => 'sheet_generic_tag_2',
                                'value' => 'item1',
                            ],
                            [
                                'tag' => 'sheet_generic_tag_2',
                                'value' => 'item3',
                            ],
                        ],
                    ],
                    [
                        'tag' => 'sheet_template_generic_tag_1',
                        'values' => [
                            [
                                'tag' => 'sheet_template_generic_tag_1',
                                'value' => 'item3',
                            ],
                            [
                                'tag' => 'sheet_template_generic_tag_1',
                                'value' => 'item5',
                            ],
                        ],
                    ],
                ],
                'messagesReceived' => [44, 128]
            ]
        );

        $this->assertEquals($expectedDocument, $transformer->transform($sheet->reveal(), []));
    }
}
