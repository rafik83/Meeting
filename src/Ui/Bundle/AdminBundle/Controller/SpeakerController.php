<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Happening\Speaker\Create;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Delete;
use Proximum\Vimeet\Application\Command\Happening\Speaker\Update;
use Proximum\Vimeet\Application\Exception\Speaker\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Speaker\CreateSpeakerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Speaker\UpdateSpeakerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SpeakerController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function listAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $speakers = $this->get('repository.happening.speaker')->allByEvent($event);

        return $this->render('AdminBundle:Speaker:list.html.twig', [
            'event'    => $event,
            'speakers' => $speakers,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $translator = $this->get('translator');

        $command = new Create($event);
        $form    = $this->createForm(CreateSpeakerType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($command);
                $this->addFlash('success', 'flash.admin.speaker.create.success');

                return $this->redirectToRoute('admin_happening_speaker_list', ['event' => $event->getId()]);
            } catch (EmailDoesNotExistException $emailDoesNotExistException) {
                $error =  new FormError(
                    $translator->trans('form.speaker.email_does_not_exist.error', [], 'forms')
                );

                $form->get('email')->addError($error);
            }
        }

        return $this->render('AdminBundle:Speaker:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Speaker $speaker
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, Speaker $speaker)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->notFoundIfWrongSpeakerEvent($event, $speaker);

        $translator = $this->get('translator');

        $command = new Update($speaker);
        $form    = $this->createForm(UpdateSpeakerType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($command);
                $this->addFlash('success', 'flash.admin.speaker.update.success');

                return $this->redirectToRoute(
                    'admin_happening_speaker_update',
                    ['event' => $event->getId(), 'speaker' => $speaker->getId()]
                );
            } catch (EmailDoesNotExistException $emailDoesNotExistException) {
                $error =  new FormError(
                    $translator->trans('form.speaker.email_does_not_exist.error', [], 'forms')
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

    /**
     * @param Event   $event
     * @param Speaker $speaker
     *
     * @return Response
     */
    public function readAction(Request $request, Event $event, Speaker $speaker)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->notFoundIfWrongSpeakerEvent($event, $speaker);

        $happenings = $this
            ->get('happening.happening_list_view_factory')
            ->getListBySpeakerAndLocale($speaker, $event->getAvailableLocale($request->getLocale()));

        return $this->render('AdminBundle:Speaker:read.html.twig', [
            'event'      => $event,
            'speaker'    => $speaker,
            'happenings' => $happenings,
        ]);
    }

    /**
     * @param Event   $event
     * @param Speaker $speaker
     *
     * @return RedirectResponse
     */
    public function deleteAction(Event $event, Speaker $speaker)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->notFoundIfWrongSpeakerEvent($event, $speaker);

        $this->get('tactician.commandbus')->handle(new Delete($speaker));
        $this->addFlash('success', 'flash.admin.speaker.delete.success');

        return $this->redirectToRoute('admin_happening_speaker_list', ['event' => $event->getId()]);
    }

    private function notFoundIfWrongSpeakerEvent(Event $event, Speaker $speaker)
    {
        if ($event !== $speaker->getEvent()) {
            throw $this->createNotFoundException('Speaker not found.');
        }
    }
}
