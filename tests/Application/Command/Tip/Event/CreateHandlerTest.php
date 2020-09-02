<?php

namespace Proximum\Vimeet\Tests\Application\Command\Tip\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Create;
use Proximum\Vimeet\Application\Command\Tip\Event\CreateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\Event\CreatedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $event->getLocales()->willReturn(['fr', 'en']);
        $dateTime = new \DateTime();

        $expected = new Tip(
            'title',
            $event->reveal(),
            true,
            false,
            true,
            false,
            true,
            true,
            true,
            false,
            true,
            $dateTime
        );
        $expected->setType($type1->reveal());
        $expected->setType($type2->reveal());
        $expected->translate('fr', 'title fr', 'content fr', $dateTime);
        $expected->translate('en', 'title en', 'content en', $dateTime);

        $tipRepository   = $this->prophesize(TipRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $tipRepository->add($expected)->shouldBeCalled();
        $eventDispatcher->dispatch(Events::TIP_EVENT_CREATED, new CreatedEvent($expected))->shouldBeCalled();

        $create = new Create($event->reveal());
        $create->title = 'title';
        $create->types = [$type1->reveal(), $type2->reveal()];
        $create->onMeetingManagement = true;
        $create->onCatalog = false;
        $create->onPrintPlanning = true;
        $create->onSheet = false;
        $create->onAgenda = true;
        $create->onPackage = true;
        $create->onContacts = true;
        $create->onProgram = false;
        $create->onConfirmationPhone = true;
        $create->translations = [
            'fr' => [
                'title' => 'title fr',
                'content' => 'content fr',
            ],
            'en' => [
                'title' => 'title en',
                'content' => 'content en',
            ],
        ];

        $handler = new CreateHandler($tipRepository->reveal(), $eventDispatcher->reveal(), $dateTime);

        $handler->handle($create);
    }
}
