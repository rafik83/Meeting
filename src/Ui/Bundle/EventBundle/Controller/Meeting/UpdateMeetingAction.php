<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Event\Update;
use Proximum\Vimeet\Application\Exception\Meeting\UpdateMeetingException;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsQuery;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Meeting\CanUpdateMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\UpdateMeetingType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class UpdateMeetingAction
{
    /** @var CanUpdateMeeting */
    private $canUpdateMeeting;

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

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CanUpdateMeeting $canUpdateMeeting,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        GetTimezoneHelper $getTimezoneHelper,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->canUpdateMeeting = $canUpdateMeeting;
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
        if (!$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || false === $this->canUpdateMeeting->isSatisfiedBy($sheet)
            || !$meeting->hasSheet($sheet)
        ) {
            throw new AccessDeniedException();
        }

        /** @var GetAvailableSlotsView $availableSlotsView */
        $availableSlotsView = $this->queryBus->handle(new GetAvailableSlotsQuery($meeting, $meeting->isVisio(), $sheet));
        $timezone = $this->getTimezoneHelper->getTimezoneByEventAndParticipant($sheet->getEvent(), $participant);

        $update = new Update($sheet, $meeting, $meeting->getParticipants($sheet), $meeting->getSlot());
        $form = $this->formFactory->create(UpdateMeetingType::class, $update, [
            'participants' => $sheet->getParticipantsArray(),
            'availableSlots' => $availableSlotsView->availableSlots,
            'timezone' => $timezone,
            'locale' => $sheet->getEvent()->getAvailableLocale($request->getLocale()),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($update);
            } catch (UpdateMeetingException $exception) {
                return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            $this->flashBag->set(
                'success',
                $this->translator->trans('agenda.meeting.update.success')
            );

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        if (0 === \count($availableSlotsView->availableSlots)) {
            return new Response(
                $this->engine->render('@Event/Meeting/no-available-slot.html.twig')
            );
        }

        return new Response(
            $this->engine->render('@Event/Meeting/update-meeting-form.html.twig', [
                'form' => $form->createView(),
                'currentSheetAvailableSlots' => $availableSlotsView->currentSheetAvailableSlotIds,
            ])
        );
    }
}
