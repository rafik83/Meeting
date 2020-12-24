<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda\Version;

use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notify;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Purge;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\User\Agenda\Version\UserPhoneNotAvailableException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Version\RetrieveUserAgendaVersion;
use Proximum\Vimeet\Application\View\Agenda\Admin\Version\UserAgendaVersionDiffView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionController extends Controller
{
    /**
     * @param Request     $request
     * @param Event       $event
     * @param Participant $participant
     *
     * @return JsonResponse
     */
    public function userDiffAction(Request $request, Event $event, Participant $participant): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($participant->getSheet()->getEvent() !== $event) {
            return $this->createErrorResponse('Wrong event', $request->getLocale());
        }

        /** @var UserAgendaVersionDiffView $view */
        $view = $this->get('tactician.commandbus.query')->handle(
            new RetrieveUserAgendaVersion($event, $participant->getUser())
        );

        return new JsonResponse([
            'answerType' => $view->state,
            'diff' => $view->diff,
        ]);
    }

    /**
     * @param Request     $request
     * @param Event       $event
     * @param Participant $participant
     *
     * @return JsonResponse
     */
    public function notifyAction(Request $request, Event $event, Participant $participant): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($participant->getSheet()->getEvent() !== $event) {
            return $this->createErrorResponse('Wrong event', $request->getLocale());
        }

        /** @var UserAgendaVersionDiffView $view */
        $view = $this->get('tactician.commandbus.query')->handle(
            new RetrieveUserAgendaVersion($event, $participant->getUser())
        );

        if ($view->hasNoPhone()) {
            return $this->createErrorResponse('gdr.user.agenda.version.modal.no_phone', $request->getLocale());
        }

        try {
            $this->get('tactician.commandbus')->handle(
                new Notify($event, $participant->getSheet(), $participant->getUser())
            );
        } catch (UserPhoneNotAvailableException $exception) {
            return $this->createErrorResponse('gdr.user.agenda.version.modal.no_phone', $request->getLocale());
        } catch (FailToSendSMSException $exception) {
            return $this->createErrorResponse('gdr.user.agenda.version.modal.fail_to_send_sms', $request->getLocale());
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request     $request
     * @param Event       $event
     * @param Participant $participant
     *
     * @return JsonResponse
     */
    public function purgeAction(Request $request, Event $event, Participant $participant): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($participant->getSheet()->getEvent() !== $event) {
            return $this->createErrorResponse('Wrong event', $request->getLocale());
        }

        /** @var UserAgendaVersionDiffView $view */
        $view = $this->get('tactician.commandbus.query')->handle(
            new RetrieveUserAgendaVersion($event, $participant->getUser())
        );

        if ($view->hasNoPhone()) {
            return $this->createErrorResponse('gdr.user.agenda.version.modal.no_phone', $request->getLocale());
        }

        $this->get('tactician.commandbus')->handle(new Purge($event, $participant->getUser()));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $message
     * @param string $locale
     *
     * @return JsonResponse
     */
    private function createErrorResponse(string $message, string $locale): JsonResponse
    {
        return new JsonResponse(
            $this->get('translator')->trans(
                $message,
                [],
                'messages',
                $locale
            ),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
