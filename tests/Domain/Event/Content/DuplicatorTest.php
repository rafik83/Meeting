<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Content;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\Content\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $eventDuplicatedFrom = EventFactory::createEvent('second event');
        $event               = EventFactory::createEvent(
            'first event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $eventDuplicatedFrom
        );

        $emptyContent = new Content($event, Content::TYPE_TERMS_OF_SALE);
        $content      = new Content($eventDuplicatedFrom, Content::TYPE_TERMS_OF_SALE);
        $content->translate('fr', 'test fr');
        $content->translate('en', 'test en');

        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $contentRepository
            ->findByEventAndType($event, Content::TYPE_TERMS_OF_SALE)
            ->shouldBeCalled()
            ->willReturn($emptyContent);

        $contentRepository
            ->findByEventAndType($eventDuplicatedFrom, Content::TYPE_TERMS_OF_SALE)
            ->shouldBeCalled()
            ->willReturn($content);

        $contentRepository->set($emptyContent)->shouldBeCalled();

        (new Duplicator($contentRepository->reveal()))->duplicate($event, Content::TYPE_TERMS_OF_SALE);
    }
}
