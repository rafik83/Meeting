<?php

namespace Application\Query\Register;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Register\PreFillUserData;
use Proximum\Vimeet\Application\Query\Register\PreFillUserDataHandler;
use Proximum\Vimeet\Domain\Account\EventParticipationPreFiller;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Event\LastEventParticipation;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class PreFillUserDataHandlerTest extends TestCase
{
    public function testHandle()
    {
        $user = UserFactory::create();
        $event = EventFactory::createEvent();
        $sheet = SheetFactory::create($event, $user);
        $lastParticipation = ParticipantFactory::create($sheet, $user);
        $locale = 'fr';
        $templateData = new TemplateData('root', [], $locale, $locale);

        $lastEventParticipation = $this->prophesize(LastEventParticipation::class);
        $eventParticipationPreFiller = $this->prophesize(EventParticipationPreFiller::class);
        $accountSynchronizer = $this->prophesize(Synchronizer::class);

        $lastEventParticipation->getLastEventParticipation($user, $event)
            ->shouldBeCalled()
            ->willReturn($lastParticipation);

        $eventParticipationPreFiller->preFillTemplate(
            $templateData,
            $lastParticipation,
            $locale
        )->shouldBeCalled()->willReturn($templateData);

        $accountSynchronizer->get($templateData, $user)
            ->shouldBeCalled()->willReturn($templateData);

        $handler = new PreFillUserDataHandler(
            $lastEventParticipation->reveal(),
            $eventParticipationPreFiller->reveal(),
            $accountSynchronizer->reveal()
        );

        $prefillUserDataView = $handler->handle(new PreFillUserData(
            $user,
            $event,
            $templateData,
            $locale
        ));

        $this->assertEquals(true, $prefillUserDataView->isParticipationDataPreFilled());
    }
}
