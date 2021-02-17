<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Schedule;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Event\Day\Update;
use Proximum\Vimeet\Application\Exception\Slot\SlotOutOfDayException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Day\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class DayController extends AbstractController
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

    public function dayAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new Update($event);
        $form   = $this->createForm(UpdateType::class, $update, [
            'event'  => $event,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($update);
                $this->addFlash('success', 'flash.schedule.days.success');

                return $this->redirectToRoute('admin_schedule_slots', [
                    'event' => $event->getId(),
                ]);
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

                $form->get('days')->addError(
                    new FormError(
                        $this->translator->trans(
                            'validators.event.day.slotOutOfDay',
                            [
                                '%begin%' => $timeFormatter->format($exception->slot->getBegin()),
                                '%end%'   => $timeFormatter->format($exception->slot->getEnd()),
                                '%day%'   => $dateFormatter->format($exception->slot->getBegin()),
                            ],
                            'validators',
                            $request->getLocale()
                        )
                    )
                );
            }
        }

        return $this->render('AdminBundle:Schedule:day.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
