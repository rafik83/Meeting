<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Create;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Delete;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Update;
use Proximum\Vimeet\Application\Components\Happening\HappeningListViewFactory;
use Proximum\Vimeet\Application\Exception\Speaker\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Speaker\CreateSpeakerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Speaker\UpdateSpeakerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class SpeakerController extends AbstractController
{
    private SpeakerRepositoryInterface $speakerRepository;
    private TranslatorInterface $translator;
    private HappeningListViewFactory $happeningListViewFactory;
    private CommandBusInterface $commandBus;

    public function __construct(
        SpeakerRepositoryInterface $speakerRepository,
        TranslatorInterface $translator,
        HappeningListViewFactory $happeningListViewFactory,
        CommandBusInterface $commandBus
    ) {
        $this->speakerRepository = $speakerRepository;
        $this->translator = $translator;
        $this->happeningListViewFactory = $happeningListViewFactory;
        $this->commandBus = $commandBus;
    }

    public function listAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $speakers = $this->speakerRepository->allByEvent($event);

        return $this->render('AdminBundle:Speaker:list.html.twig', [
            'event'    => $event,
            'speakers' => $speakers,
        ]);
    }

    public function createAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new Create($event);
        $form    = $this->createForm(CreateSpeakerType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($command);
                $this->addFlash('success', 'flash.admin.speaker.create.success');

                return $this->redirectToRoute('admin_happening_speaker_list', ['event' => $event->getId()]);
            } catch (EmailDoesNotExistException $emailDoesNotExistException) {
                $error =  new FormError(
                    $this->translator->trans('form.speaker.email_does_not_exist.error', [], 'forms')
                );

                $form->get('email')->addError($error);
            }
        }

        return $this->render('AdminBundle:Speaker:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    public function updateAction(Request $request, Event $event, Speaker $speaker): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new Update($speaker);
        $form    = $this->createForm(UpdateSpeakerType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($command);
                $this->addFlash('success', 'flash.admin.speaker.update.success');

                return $this->redirectToRoute(
                    'admin_happening_speaker_update',
                    ['event' => $event->getId(), 'speaker' => $speaker->getId()]
                );
            } catch (EmailDoesNotExistException $emailDoesNotExistException) {
                $error =  new FormError(
                    $this->translator->trans('form.speaker.email_does_not_exist.error', [], 'forms')
                );

                $form->get('email')->addError($error);
            }
        }

        return $this->render('AdminBundle:Speaker:update.html.twig', [
            'event'   => $event,
            'speaker' => $speaker,
            'form'    => $form->createView(),
        ]);
    }

    public function readAction(Request $request, Event $event, Speaker $speaker): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $happenings = $this->happeningListViewFactory
            ->getListBySpeakerAndLocale($speaker, $event->getAvailableLocale($request->getLocale()));

        return $this->render('AdminBundle:Speaker:read.html.twig', [
            'event'      => $event,
            'speaker'    => $speaker,
            'happenings' => $happenings,
        ]);
    }

    public function deleteAction(Event $event, Speaker $speaker): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $this->commandBus->handle(new Delete($speaker));
        $this->addFlash('success', 'flash.admin.speaker.delete.success');

        return $this->redirectToRoute('admin_happening_speaker_list', ['event' => $event->getId()]);
    }
}
