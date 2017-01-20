<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Messaging;

use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Domain\Messaging\InvalidMessagePlaceholderException;
use Proximum\Vimeet\Domain\Messaging\SubstitutionsProvider;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Compose;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class SubstitutionsProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var SubstitutionsProvider */
    private $substitutionProvider;

    /** @var Event\EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ActivateAccountTokenGenerator */
    private $activateAccountTokenGenerator;

    public function setUp()
    {
        $this->eventUrlGenerator             = $this->prophesize(Event\EventUrlGeneratorInterface::class);
        $this->participantInfoGuesser        = $this->prophesize(ParticipantInfoGuesser::class);
        $this->activateAccountTokenGenerator = $this->prophesize(ActivateAccountTokenGenerator::class);

        $this->substitutionProvider = new SubstitutionsProvider(
            $this->eventUrlGenerator->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->activateAccountTokenGenerator->reveal()
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
                [Compose::TAG_EVENT_NAME, Compose::LINK_AGENDA, Compose::LINK_PACKAGE]
            ],
            [
                sprintf('%s %s', Compose::LINK_SHEET, Compose::TAG_PARTICIPANT),
                [Compose::TAG_PARTICIPANT, Compose::LINK_SHEET]
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
        $sheet->getEvent()->willReturn($event);

        $placeholders = [Compose::TAG_EVENT_NAME, "%INVALID-PLACEHOLDER%"];

        $this->substitutionProvider->getSubstitutions($recipient, $sheet->reveal(), $locale, $placeholders);
    }

    public function testGetSubstitutionsForParticipant()
    {
        $recipient = $this->prophesize(Participant::class);
        $sheet     = $this->prophesize(Sheet::class);
        $event     = $this->prophesize(Event::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $locale    = 'fr';
        $event->getAvailableLocale($locale)->willReturn($locale);

        $placeholders = [Compose::TAG_PARTICIPANT, Compose::LINK_AGENDA];
        $this->participantInfoGuesser->guessParticipantCompleteName($recipient->reveal(), $locale)->willReturn('Henri Désiré Landru');
        $this->eventUrlGenerator->generateEventAbsoluteUrl($event->reveal(), 'event_agenda', [])->willReturn('url-to-event-agenda');

        $this->assertEquals(
            [
                Compose::TAG_PARTICIPANT => 'Henri Désiré Landru',
                Compose::LINK_AGENDA     => 'url-to-event-agenda',
            ],
            $this->substitutionProvider->getSubstitutions($recipient->reveal(), $sheet->reveal(), $locale, $placeholders)
        );
    }
}
