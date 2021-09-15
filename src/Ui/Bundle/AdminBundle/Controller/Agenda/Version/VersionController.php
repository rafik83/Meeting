<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda\Version;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notify;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Purge;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\User\Agenda\Version\UserPhoneNotAvailableException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Version\RetrieveUserAgendaVersion;
use Proximum\Vimeet\Application\View\Agenda\Admin\Version\UserAgendaVersionDiffView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VersionController extends AbstractController
{
    private TranslatorInterface $translator;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        TranslatorInterface $translator,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->translator = $translator;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function userDiffAction(Request $request, Event $event, Participant $participant): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($participant->getSheet()->getEvent() !== $event) {
            return $this->createErrorResponse('Wrong event', $request->getLocale());
        }

        /** @var UserAgendaVersionDiffView $view */
        $view = $this->queryBus->handle(
            new RetrieveUserAgendaVersion($event, $participant->getUser())
        );

        return new JsonResponse([
            'answerType' => $view->state,
            'diff' => $view->diff,
        ]);
    }

    public function notifyAction(Request $request, Event $event, Participant $participant): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($participant->getSheet()->getEvent() !== $event) {
            return $this->createErrorResponse('Wrong event', $request->getLocale());
        }

        /** @var UserAgendaVersionDiffView $view */
        $view = $this->queryBus->handle(
            new RetrieveUserAgendaVersion($event, $participant->getUser())
        );

        if ($view->hasNoPhone()) {
            return $this->createErrorResponse('gdr.user.agenda.version.modal.no_phone', $request->getLocale());
        }

        try {
            $this->commandBus->handle(
                new Notify($event, $participant->getSheet(), $participant->getUser())
            );
        } catch (UserPhoneNotAvailableException $exception) {
            return $this->createErrorResponse('gdr.user.agenda.version.modal.no_phone', $request->getLocale());
        } catch (FailToSendSMSException $exception) {
            return $this->createErrorResponse('gdr.user.agenda.version.modal.fail_to_send_sms', $request->getLocale());
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    public function purgeAction(Request $request, Event $event, Participant $participant): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($participant->getSheet()->getEvent() !== $event) {
            return $this->createErrorResponse('Wrong event', $request->getLocale());
        }

        /** @var UserAgendaVersionDiffView $view */
        $view = $this->queryBus->handle(
            new RetrieveUserAgendaVersion($event, $participant->getUser())
        );

        if ($view->hasNoPhone()) {
            return $this->createErrorResponse('gdr.user.agenda.version.modal.no_phone', $request->getLocale());
        }

        $this->commandBus->handle(new Purge($event, $participant->getUser()));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function createErrorResponse(string $message, string $locale): JsonResponse
    {
        return new JsonResponse(
            $this->translator->trans(
                $message,
                [],
                'messages',
                $locale
            ),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
