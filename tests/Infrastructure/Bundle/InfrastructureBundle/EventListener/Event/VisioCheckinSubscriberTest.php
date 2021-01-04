<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Event\VisioCheckinSubscriber;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class VisioCheckinSubscriberTest extends TestCase
{
    public function testOnKernelRequest(): void
    {
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(1);

        $event = EventFactory::createEvent();
        $request = Request::create('/sheet/1');
        $request->setDefaultLocale('fr');

        $request->headers->set('HOST', $event->getDomain());
        $request->attributes->set('sheet', 1);
        $request->attributes->set('_route', 'event_sheet_default');

        $eventByHostResolver = $this->prophesize(EventByHostResolver::class);
        $dayGuesser = $this->prophesize(DDayGuesser::class);
        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $scanRepository = $this->prophesize(ScanRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $date = new \DateTime();

        $token = $this->prophesize(TokenInterface::class);
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $tokenStorage = $this->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()
            ->shouldBeCalled()
            ->willReturn($token);

        $participant = $this->prophesize(Participant::class);

        $sheetRepository->getSheetById(1)
            ->shouldBeCalled()
            ->willReturn($sheet->reveal());

        $sheet->getUserParticipant($user->reveal())
            ->shouldBeCalled()
            ->willReturn($participant->reveal());

        $eventByHostResolver->resolveEventFromHostAndLocale($event->getDomain(), 'fr')
            ->shouldBeCalled()
            ->willReturn($event);

        $dayGuesser->isItDDay($event)
            ->shouldBeCalled()
            ->willReturn(true);

        $scanRepository->isUserCheckinTodayByEvent($user->reveal(), $event, $date)
            ->shouldBeCalled()
            ->willReturn(false);

        $urlGenerator->generate('event_visio_checkin', ['sheet' => 1])
            ->shouldBeCalled();

        $responseEvent = new GetResponseEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
        $isParticipantVisio->isSatisfiedBy($participant->reveal())->willReturn(true);

        $subscriber = new VisioCheckinSubscriber(
            $eventByHostResolver->reveal(),
            $dayGuesser->reveal(),
            $urlGenerator->reveal(),
            $scanRepository->reveal(),
            $sheetRepository->reveal(),
            $tokenStorage->reveal(),
            $date,
            $isParticipantVisio->reveal()
        );

        $subscriber->onKernelRequest($responseEvent);
    }
}
