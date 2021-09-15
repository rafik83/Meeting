<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Domain\Messaging\GetSheetsReceivers;
use Proximum\Vimeet\Domain\Messaging\SubstitutionsProvider;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class GetSheetsReceiversTest extends TestCase
{
    public function test__invoke():void
    {
        $participant = $this->prophesize(Participant::class);
        $participant->getLocale()->shouldBeCalled()->willReturn('fr');
        $participant->getEmail()->shouldBeCalled()->willReturn('user@example.net');

        $event = $this->prophesize(Event::class);
        $event->getLocales()->shouldBeCalled()->willReturn(['fr']);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getParticipants()->shouldBeCalled()->willReturn([$participant->reveal()]);

        $message = $this->prophesize(Message::class);
        $message->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $message->getContent('fr')->shouldBeCalled()->willReturn('content %participant% FR');

        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);

        $substitutionsProvider = $this->prophesize(SubstitutionsProvider::class);
        $substitutionsProvider->findPlaceholdersInMessage('content %participant% FR')
            ->shouldBeCalled()
            ->willReturn(['%participant%']);
        $substitutionsProvider->getSubstitutions($participant->reveal(), $sheet->reveal(), 'fr', ['%participant%'])
            ->shouldBeCalled()
            ->willReturn(['%participant%' => 'John Doe']);

        $getSheetsReceivers = new GetSheetsReceivers($billingInfoRepository->reveal(), $substitutionsProvider->reveal());
        $result = $getSheetsReceivers([$sheet->reveal()], [Campaign::RECIPIENT_PARTICIPANTS], $message->reveal());
        $expectedResult = [
            'user@example.net' => new ReceiverView(
                'user@example.net',
                ['%participant%' => 'John Doe'],
                'fr'
            )
        ];

        $this->assertEquals($result, $expectedResult);
    }
}
