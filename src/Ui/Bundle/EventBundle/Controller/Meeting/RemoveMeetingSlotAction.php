<?php
/**
 * Created by PhpStorm.
 * User: taner
 * Date: 18/03/19
 * Time: 16:15
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Meeting;

use Proximum\Vimeet\Domain\Meeting\CanRemoveMeeting;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Exception\Meeting\RemoveMeetingSlotException;
use Proximum\Vimeet\Application\Query\MeetingSlot\GetAvailableSlotsView;
use Proximum\Vimeet\Application\Command\MeetingSlot\Remove;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MeetingSlot\RemoveMeetingSlotType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class RemoveMeetingSlotAction
{
    /** @var CanRemoveMeeting */
    private $canRemoveMeeting;

    /** @var FormFactoryInterface $formFactory */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        CanRemoveMeeting $canRemoveMeeting,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->canRemoveMeeting = $canRemoveMeeting;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Participant $participant, Sheet $sheet, Meeting $meeting): Response
    {
        if (false === $this->canRemoveMeeting->isSatisfiedBy($sheet)) {
            throw new AccessDeniedException();
        }

        $remove = new Remove($meeting->getSlot(), $sheet, $meeting);
        $form = $this->formFactory->create(RemoveMeetingSlotType::class, $remove, [
            'locale' => $sheet->getEvent()->getAvailableLocale($request->getLocale()),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($remove);
            } catch (RemoveMeetingSlotException $exception) {
                return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            $this->flashBag->set(
                'success',
                $this->translator->trans('agenda.meeting.remove.success')
            );

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

            return new Response(
                $this->engine->render('@Event/Meeting/remove-meeting-slot-form.html.twig', [
                    'form' => $form->createView(),
                ])
            );
    }
}
