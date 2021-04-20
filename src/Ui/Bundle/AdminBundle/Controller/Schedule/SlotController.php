<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Schedule;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\MeetingSlot\Generate;
use Proximum\Vimeet\Application\Command\MeetingSlot\Lock;
use Proximum\Vimeet\Application\Command\MeetingSlot\Remove;
use Proximum\Vimeet\Application\Command\MeetingSlot\Unlock;
use Proximum\Vimeet\Application\Exception\Slot\IsNotAllowedToRemoveSlotException;
use Proximum\Vimeet\Application\Exception\Slot\SlotOutOfDayException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Schedule\GenerateType;
use Proximum\Vimeet\Ui\Flash\TranschoiceMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SlotController extends AbstractController
{
    private TranslatorInterface $translator;
    private CommandBusInterface $commandBus;

    public function __construct(
        TranslatorInterface $translator,
        CommandBusInterface $commandBus
    ) {
        $this->translator = $translator;
        $this->commandBus = $commandBus;
    }

    public function generateAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if (!$event->hasDay()) {
            $this->addFlash('error', 'flash.schedule.slot.generate.missingDay');

            return $this->redirectToRoute('admin_schedule_slots', ['event' => $event->getId()]);
        }

        $command = new Generate($event);
        $form    = $this->createForm(GenerateType::class, $command, ['submit' => true, 'event' => $event]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $result = $this->commandBus->handle($command);
                $this->addFlash(
                    'success',
                    new TranschoiceMessage('flash.schedule.slot.generate.success', $result->count, [
                        '%count%' => $result->count,
                    ])
                );

                return $this->redirectToRoute('admin_schedule_slots', ['event' => $event->getId()]);
            } catch (SlotOutOfDayException $exception) {
                $timeFormatter = \IntlDateFormatter::create(
                    $request->getLocale(),
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::SHORT,
                    $event->getTimeZone()
                );

                $dateFormatter = \IntlDateFormatter::create(
                    $request->getLocale(),
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::NONE,
                    $event->getTimeZone()
                );

                $form->get('recipes')
                    ->addError(new FormError(
                        $this->translator->trans(
                            'validators.schedule.slot.recipes.slotOutOfDay',
                            [
                                '%day%'   => $dateFormatter->format($exception->slot->getBegin()),
                                '%begin%' => $timeFormatter->format($exception->slot->getBegin()),
                                '%end%'   => $timeFormatter->format($exception->slot->getEnd()),
                            ],
                            'validators',
                            $request->getLocale()
                        ))
                    )
                ;
            }
        }

        return $this->render('AdminBundle:Schedule:generate.html.twig', [
            'days'  => $event->getDays(),
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Event       $event
     * @param MeetingSlot $meetingSlot
     *
     * @return RedirectResponse
     */
    public function lockAction(Event $event, MeetingSlot $meetingSlot)
    {
        return $this->handleAndRedirect($event, $meetingSlot, new Lock($meetingSlot));
    }

    /**
     * @param Event       $event
     * @param MeetingSlot $meetingSlot
     *
     * @return RedirectResponse
     */
    public function unlockAction(Event $event, MeetingSlot $meetingSlot)
    {
        return $this->handleAndRedirect($event, $meetingSlot, new Unlock($meetingSlot));
    }

    /**
     * @param Event       $event
     * @param MeetingSlot $meetingSlot
     *
     * @return RedirectResponse
     */
    public function removeAction(Event $event, MeetingSlot $meetingSlot)
    {
        return $this->handleAndRedirect($event, $meetingSlot, new Remove($meetingSlot));
    }

    /**
     * @param Event       $event
     * @param MeetingSlot $meetingSlot
     */
    private function denyAccessIfWrongEvent(Event $event, MeetingSlot $meetingSlot)
    {
        if ($meetingSlot->getEvent() !== $event) {
            throw $this->createAccessDeniedException('This meeting slot is not available for this event.');
        }
    }

    /**
     * @param Event       $event
     * @param MeetingSlot $meetingSlot
     * @param mixed       $command
     *
     * @return RedirectResponse
     */
    private function handleAndRedirect(Event $event, MeetingSlot $meetingSlot, $command)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessIfWrongEvent($event, $meetingSlot);

        try {
            $this->commandBus->handle($command);
        } catch (IsNotAllowedToRemoveSlotException $isNotAllowedToRemoveSlotException) {
            $this->addFlash('error', 'flash.admin.slot.remove.error');
        }

        return $this->redirectToRoute('admin_schedule_slots', ['event' => $meetingSlot->getEvent()->getId()]);
    }
}
