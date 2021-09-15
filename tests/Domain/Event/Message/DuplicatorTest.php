<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Message;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\Message\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $eventDuplicated = EventFactory::createEvent('event duplicated');
        $event           = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );
        $date = new \DateTime();

        $oldMessage = new Message(
            $eventDuplicated,
            $date,
            'message name',
            true,
            true
        );

        $oldMessage->translate('fr', 'sujet', 'contenu', $date);
        $oldMessage->translate('en', 'subject', 'content', $date);

        $expectedMessage = new Message(
            $event,
            $date,
            'message name',
            true,
            true
        );

        $expectedMessage->translate('fr', 'sujet', 'contenu', $date);
        $expectedMessage->translate('en', 'subject', 'content', $date);

        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->findByEvent($eventDuplicated)->shouldBeCalled()->willReturn([$oldMessage]);
        $messageRepository->add($expectedMessage)->shouldBeCalled();

        (new Duplicator($messageRepository->reveal(), $date))->duplicate($event);
    }
}
