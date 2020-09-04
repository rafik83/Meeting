<?php

namespace Proximum\Vimeet\Tests\Application\Command\Tip\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Update;
use Proximum\Vimeet\Application\Command\Tip\Event\UpdateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\Event\UpdatedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $oldType1 = $this->prophesize(Type::class);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $event->getLocales()->willReturn(['fr', 'en']);
        $dateTime = new \DateTime();
        $oldDateTime = new \DateTime('2016-10-10 10:00:00.000');

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
            $oldDateTime
        );
        $expected->setType($type1->reveal());
        $expected->setType($type2->reveal());
        $expected->translate('fr', 'title fr', 'content fr', $dateTime);
        $expected->translate('en', 'title en', 'content en', $dateTime);

        $tip = new Tip(
            'old title',
            $event->reveal(),
            false,
            true,
            false,
            true,
            false,
            false,
            false,
            true,
            false,
            $oldDateTime
        );

        $tip->setType($oldType1->reveal());
        $tip->setType($type2->reveal());
        $tip->translate('fr', 'old title fr', 'old content fr', $oldDateTime);
        // no "en" translation, to test that if an event has a new locale, the tip can be translated with it.

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $tipRepository->set(Argument::that(static function (Tip $tip) use ($expected) {
            return $tip->getTitle() === $expected->getTitle()
                && $tip->getEvent() === $expected->getEvent()
                && $tip->isOnMeetingManagement() === $expected->isOnMeetingManagement()
                && $tip->isOnCatalog() === $expected->isOnCatalog()
                && $tip->isOnPrintPlanning() === $expected->isOnPrintPlanning()
                && $tip->isOnSheet() === $expected->isOnSheet()
                && $tip->isOnAgenda() === $expected->isOnAgenda()
                && $tip->isOnPackage() === $expected->isOnPackage()
                && $tip->isOnContacts() === $expected->isOnContacts()
                && $tip->isOnProgram() === $expected->isOnProgram()
                && $tip->isOnConfirmationPhone() === $expected->isOnConfirmationPhone()
                && $tip->getTranslationTitle('fr') === $expected->getTranslationTitle('fr')
                && $tip->getTranslationTitle('en') === $expected->getTranslationTitle('en')
                && $tip->getTranslationContent('fr') === $expected->getTranslationContent('fr')
                && $tip->getTranslationContent('en') === $expected->getTranslationContent('en')
            ;
        }))->shouldBeCalled();
        $delayedEventDispatcher->dispatch(
            Events::TIP_EVENT_UPDATED,
            Argument::that(static function (UpdatedEvent $event) use ($expected) {
                return $event->getTip()->getTitle() === $expected->getTitle();
            })
        )->shouldBeCalled();

        $update = new Update($tip);
        $update->title = 'title';
        $update->types = [$type1->reveal(), $type2->reveal()];
        $update->onMeetingManagement = true;
        $update->onCatalog = false;
        $update->onPrintPlanning = true;
        $update->onSheet = false;
        $update->onAgenda = true;
        $update->onPackage = true;
        $update->onContacts = true;
        $update->onProgram = false;
        $update->onConfirmationPhone = true;
        $update->translations = [
            'fr' => [
                'title' => 'title fr',
                'content' => 'content fr',
            ],
            'en' => [
                'title' => 'title en',
                'content' => 'content en',
            ],
        ];
        $handler = new UpdateHandler(
            $tipRepository->reveal(),
            $delayedEventDispatcher->reveal(),
            $dateTime
        );
        $handler->handle($update);
    }
}
