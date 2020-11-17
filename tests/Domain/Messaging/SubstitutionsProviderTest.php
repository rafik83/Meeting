<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Query\Sheet\Planning\SheetPlanningViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Planning\SheetPlanningViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Planning\SheetPlanningView;
use Proximum\Vimeet\Domain\Messaging\InvalidMessagePlaceholderException;
use Proximum\Vimeet\Domain\Messaging\Substitutions\AgendaConfirmationCTASubstitution;
use Proximum\Vimeet\Domain\Messaging\Substitutions\DownloadEBadgeCTASubstitution;
use Proximum\Vimeet\Domain\Messaging\Substitutions\TestVisioConfigurationCTASubstitution;
use Proximum\Vimeet\Domain\Messaging\SubstitutionsProvider;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Compose;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SubstitutionsProviderTest extends TestCase
{
    /** @var SubstitutionsProvider */
    private $substitutionProvider;

    /** @var Event\EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ActivateAccountTokenGenerator */
    private $activateAccountTokenGenerator;

    /** @var SheetPlanningViewQueryHandler */
    private $sheetPlanningViewQueryHandler;

    /** @var AgendaConfirmationCTASubstitution */
    private $agendaConfirmationCTASubstitution;

    /** @var ObjectProphecy */
    private $downloadEBadgeCTASubstitution;

    /** @var ObjectProphecy */
    private $testVisioConfigurationCTASubstitution;

    public function setUp()
    {
        $this->eventUrlGenerator                 = $this->prophesize(Event\EventUrlGeneratorInterface::class);
        $this->participantInfoGuesser            = $this->prophesize(ParticipantInfoGuesser::class);
        $this->activateAccountTokenGenerator     = $this->prophesize(ActivateAccountTokenGenerator::class);
        $this->sheetPlanningViewQueryHandler     = $this->prophesize(SheetPlanningViewQueryHandler::class);
        $this->agendaConfirmationCTASubstitution = $this->prophesize(AgendaConfirmationCTASubstitution::class);
        $this->downloadEBadgeCTASubstitution = $this->prophesize(DownloadEBadgeCTASubstitution::class);
        $this->testVisioConfigurationCTASubstitution = $this->prophesize(TestVisioConfigurationCTASubstitution::class);

        $this->substitutionProvider = new SubstitutionsProvider(
            $this->eventUrlGenerator->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->activateAccountTokenGenerator->reveal(),
            $this->sheetPlanningViewQueryHandler->reveal(),
            $this->agendaConfirmationCTASubstitution->reveal(),
            $this->downloadEBadgeCTASubstitution->reveal(),
            $this->testVisioConfigurationCTASubstitution->reveal()
        );
    }

    /**
     * @dataProvider providePlaceholdersData
     */
    public function testFindPlaceholdersInMessage($messageBody, $expectedPlaceholders)
    {
        $this->assertEquals($this->substitutionProvider->findPlaceholdersInMessage($messageBody), $expectedPlaceholders);
    }

    public function providePlaceholdersData()
    {
        return [
            [
                sprintf('%s %s %s', Compose::TAG_EVENT_NAME, Compose::LINK_PACKAGE, Compose::LINK_AGENDA),
                [Compose::TAG_EVENT_NAME, Compose::LINK_AGENDA, Compose::LINK_PACKAGE],
            ],
            [
                sprintf('%s %s', Compose::LINK_SHEET, Compose::TAG_PARTICIPANT),
                [Compose::TAG_PARTICIPANT, Compose::LINK_SHEET],
            ],
        ];
    }

    public function testExceptionIfInvalidPlaceholder()
    {
        $this->expectException(InvalidMessagePlaceholderException::class);

        $locale    = 'fr';
        $recipient = $this->prophesize(MailRecipientInterface::class)->reveal();
        $sheet     = $this->prophesize(Sheet::class);
        $event     = $this->prophesize(Event::class);
        $event->getTitle()->willReturn('Lorem ipsum');
        $sheet->getEvent()->willReturn($event);

        $placeholders = [Compose::TAG_EVENT_NAME, '%INVALID-PLACEHOLDER%'];

        $this->substitutionProvider->getSubstitutions($recipient, $sheet->reveal(), $locale, $placeholders);
    }

    public function testGetSubstitutionsForParticipant()
    {
        $recipient = $this->prophesize(Participant::class);
        $sheet     = $this->prophesize(Sheet::class);
        $event     = $this->prophesize(Event::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getId()->willReturn(1);
        $locale    = 'fr';
        $event->getAvailableLocale($locale)->willReturn($locale);

        $placeholders = [Compose::TAG_PARTICIPANT, Compose::LINK_AGENDA];
        $this->participantInfoGuesser->guessParticipantCompleteName($recipient->reveal(), $locale)->willReturn('Henri Désiré Landru');
        $this->eventUrlGenerator->generateEventAbsoluteUrl($event->reveal(), 'event_agenda', ['sheet' => 1, '_locale' => 'fr'])->willReturn('url-to-event-agenda');

        $this->assertEquals(
            [
                Compose::TAG_PARTICIPANT => 'Henri Désiré Landru',
                Compose::LINK_AGENDA     => 'url-to-event-agenda',
            ],
            $this->substitutionProvider->getSubstitutions($recipient->reveal(), $sheet->reveal(), $locale, $placeholders)
        );
    }

    public function testGetSubstitutionForSheetSpot()
    {
        $recipient = $this->prophesize(Participant::class);
        $sheet     = $this->prophesize(Sheet::class);
        $event     = $this->prophesize(Event::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getId()->willReturn(1);
        $locale    = 'fr';
        $event->getAvailableLocale($locale)->willReturn($locale);
        $sheet->hasSpot()->shouldBeCalled()->willReturn(true);
        $spot = $this->prophesize(Spot::class);
        $spot->getReference()->shouldBeCalled()->willReturn('A123');
        $sheet->getSpot()->willReturn($spot->reveal());

        $placeholders = [Compose::TAG_PARTICIPANT, Compose::LINK_AGENDA, Compose::TAG_SHEET_SPOT];
        $this->participantInfoGuesser->guessParticipantCompleteName($recipient->reveal(), $locale)->willReturn('Henri Désiré Landru');
        $this->eventUrlGenerator->generateEventAbsoluteUrl($event->reveal(), 'event_agenda', ['sheet' => 1, '_locale' => 'fr'])->willReturn('url-to-event-agenda');

        $this->assertEquals(
            [
                Compose::TAG_PARTICIPANT => 'Henri Désiré Landru',
                Compose::LINK_AGENDA     => 'url-to-event-agenda',
                Compose::TAG_SHEET_SPOT => 'A123',
            ],
            $this->substitutionProvider->getSubstitutions($recipient->reveal(), $sheet->reveal(), $locale, $placeholders)
        );
    }

    public function testGetSubstitutionWithoutSheetSpot()
    {
        $recipient = $this->prophesize(Participant::class);
        $sheet     = $this->prophesize(Sheet::class);
        $event     = $this->prophesize(Event::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getId()->willReturn(1);
        $locale    = 'fr';
        $event->getAvailableLocale($locale)->willReturn($locale);
        $sheet->hasSpot()->shouldBeCalled()->willReturn(false);
        $sheet->getSpot()->shouldNotBeCalled();

        $placeholders = [Compose::TAG_PARTICIPANT, Compose::LINK_AGENDA, Compose::TAG_SHEET_SPOT];
        $this->participantInfoGuesser->guessParticipantCompleteName($recipient->reveal(), $locale)->willReturn('Henri Désiré Landru');
        $this->eventUrlGenerator->generateEventAbsoluteUrl($event->reveal(), 'event_agenda', ['sheet' => 1, '_locale' => 'fr'])->willReturn('url-to-event-agenda');

        $this->assertEquals(
            [
                Compose::TAG_PARTICIPANT => 'Henri Désiré Landru',
                Compose::LINK_AGENDA     => 'url-to-event-agenda',
                Compose::TAG_SHEET_SPOT => '',
            ],
            $this->substitutionProvider->getSubstitutions($recipient->reveal(), $sheet->reveal(), $locale, $placeholders)
        );
    }

    public function testGetSubstitutionsForCTA()
    {
        $recipient = $this->prophesize(Participant::class);
        $sheet     = $this->prophesize(Sheet::class);
        $event     = $this->prophesize(Event::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $sheet->getId()->willReturn(1);
        $locale    = 'fr';
        $event->getAvailableLocale($locale)->willReturn($locale);

        $placeholders = [Compose::TAG_PARTICIPANT, Compose::LINK_AGENDA, Compose::TAG_CTA_EBADGE];
        $this->participantInfoGuesser->guessParticipantCompleteName($recipient->reveal(), $locale)->willReturn('Henri Désiré Landru');
        $this->eventUrlGenerator->generateEventAbsoluteUrl($event->reveal(), 'event_agenda', ['sheet' => 1, '_locale' => 'fr'])->willReturn('url-to-event-agenda');
        $this->downloadEBadgeCTASubstitution->getCTA($recipient->reveal(), $sheet->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn('<a href="/download">Download</a>')
        ;

        $this->assertEquals(
            [
                Compose::TAG_PARTICIPANT => 'Henri Désiré Landru',
                Compose::LINK_AGENDA     => 'url-to-event-agenda',
                Compose::TAG_CTA_EBADGE => '<a href="/download">Download</a>',
            ],
            $this->substitutionProvider->getSubstitutions($recipient->reveal(), $sheet->reveal(), $locale, $placeholders)
        );
    }

    public function testGetSubstitutionsPlanning()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $recipient = $this->prophesize(Participant::class);
        $sheet     = $this->prophesize(Sheet::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event);
        $sheet->getId()->shouldBeCalled()->willReturn(1);

        $placeholders = [Compose::TAG_PARTICIPANT, Compose::LINK_AGENDA, Compose::TAG_SHEET_PLANNING];
        $this->participantInfoGuesser->guessParticipantCompleteName($recipient, $locale)->willReturn('Henri Désiré Landru');
        $this->eventUrlGenerator->generateEventAbsoluteUrl($event, 'event_agenda', ['sheet' => 1, '_locale' => 'fr'])->willReturn('url-to-event-agenda');
        $this->sheetPlanningViewQueryHandler
            ->handle(new SheetPlanningViewQuery($sheet->reveal(), $locale, $recipient->reveal()))
            ->shouldBeCalled()
            ->willReturn(new SheetPlanningView('<p>PLANNING</p>'));

        $this->assertEquals(
            [
                Compose::TAG_PARTICIPANT    => 'Henri Désiré Landru',
                Compose::LINK_AGENDA        => 'url-to-event-agenda',
                Compose::TAG_SHEET_PLANNING => '<p>PLANNING</p>',
            ],
            $this->substitutionProvider->getSubstitutions($recipient->reveal(), $sheet->reveal(), $locale, $placeholders)
        );
    }
}
