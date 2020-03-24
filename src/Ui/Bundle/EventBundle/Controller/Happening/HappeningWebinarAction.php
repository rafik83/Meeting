<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\View\Happening\WebinarView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Happening\ParticipationVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class HappeningWebinarAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;
    /** @var EngineInterface */
    private $engine;
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EngineInterface $engine,
        VideoConferenceAdapterInterface $videoConferenceAdapter
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->engine = $engine;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Happening $happening,
        UserDomain $userDomain
    ): Response {
        $event = $eventDomain->getEvent();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->authorizationCheckerAdapter->isGranted(ParticipationVoter::PARTICIPATE, $sheet)
        ) {
            throw new AccessDeniedException('Access denied to this happening');
        }

        $user = $userDomain->getUser();

        if ($happening->getEvent() !== $event || $sheet->getEvent() !== $event) {
            throw new NotFoundHttpException('Happening or sheet not in this event');
        }

        $session = $this->videoConferenceAdapter->createSession();
        $token = $this->videoConferenceAdapter->generateAccessToken(
            $session,
            $happening->getEnd()
        );
        $sessionId = $session->getSessionId();

        $isSpeaker = false;

        $webinarView = new WebinarView(
            $token,
            $sessionId,
            $this->videoConferenceAdapter->getApiKey(),
            $isSpeaker,
            new TimeRangeView($happening->getBegin(), $happening->getEnd()),
            new \DateTime()
        );

        return new Response(
            $this->engine->render(
                $webinarView->isSpeaker
                    ? '@Event/Happening/webinar-speaker.html.twig'
                    : '@Event/Happening/webinar-viewer.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'userCompleteName' => $user->getAccount()->getCompleteName(),
                    'webinarView' => $webinarView,
                ]
            )
        );
    }
}
