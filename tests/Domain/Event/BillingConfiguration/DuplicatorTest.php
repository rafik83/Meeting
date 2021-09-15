<?php

namespace Proximum\Vimeet\Tests\Domain\Event\BillingConfiguration;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Event\BillingConfiguration\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $oldEvent = EventFactory::createEvent('second event');
        $oldEvent->setInvoiceLogo('testLogo', 'png');
        $newEvent = EventFactory::createEvent(
            'first event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $oldEvent
        );
        $newEvent->getTranslations()->set('fr', new EventTranslation($newEvent, 'fr', 'description fr'));
        $newEvent->getTranslations()->set('en', new EventTranslation($newEvent, 'en', 'description en'));

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->copyAndRename('testLogo')->shouldBeCalled()->willReturn('newLogoName');

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($newEvent)->shouldBeCalled();

        (new Duplicator($eventRepository->reveal(), $fileStorage->reveal()))->duplicate($newEvent);
    }
}
