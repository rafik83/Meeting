<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\MeetingSlot\Move;
use Proximum\Vimeet\Application\Exception\Meeting\MoveMeetingSlotException;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsQuery;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Meeting\CanMoveMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MeetingSlot\MoveMeetingSlotType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class MoveMeetingSlotAction
{
    /** @var CanMoveMeeting */
    private $canMoveMeeting;

    /** @var FormFactoryInterface $formFactory */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        CanMoveMeeting $canMoveMeeting,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        GetTimezoneHelper $getTimezoneHelper,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->canMoveMeeting = $canMoveMeeting;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->getTimezoneHelper = $getTimezoneHelper;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Participant $participant, Sheet $sheet, Meeting $meeting): Response
    {
        if (false === $this->canMoveMeeting->isSatisfiedBy($sheet)) {
            throw new AccessDeniedException();
        }

        /** @var GetAvailableSlotsView $availableSlotsView */
        $availableSlotsView = $this->queryBus->handle(new GetAvailableSlotsQuery($meeting));
        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndParticipant($sheet->getEvent(), $participant);

        $move = new Move($sheet, $meeting);
        $form = $this->formFactory->create(MoveMeetingSlotType::class, $move, [
            'availableSlots' => $availableSlotsView->availableSlots,
            'timezone' => $timezone,
            'locale' => $sheet->getEvent()->getAvailableLocale($request->getLocale()),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($move);
            } catch (MoveMeetingSlotException $exception) {
                return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            $this->flashBag->set(
                'success',
                $this->translator->trans('agenda.meeting.move.success')
            );

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        return new Response(
            $this->engine->render('@Event/Meeting/move-meeting-slot-form.html.twig', [
                'form' => $form->createView(),
                'hasAvailableSlots' => \count($availableSlotsView->availableSlots) > 0,
            ])
        );
    }
}
