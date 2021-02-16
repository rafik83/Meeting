<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Meeting\CanRemoveMeeting;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Exception\Meeting\RemoveMeetingException;
use Proximum\Vimeet\Application\Command\Meeting\Event\Remove;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\RemoveMeetingType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class RemoveMeetingAction
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

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CanRemoveMeeting $canRemoveMeeting,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->canRemoveMeeting = $canRemoveMeeting;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Participant $participant, Sheet $sheet, Meeting $meeting): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || false === $this->canRemoveMeeting->isSatisfiedBy($sheet)
            || !$meeting->hasSheet($sheet)
        ) {
            throw new AccessDeniedException();
        }

        $remove = new Remove($sheet, $meeting);
        $form = $this->formFactory->create(RemoveMeetingType::class, $remove);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($remove);
            } catch (RemoveMeetingException $exception) {
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
