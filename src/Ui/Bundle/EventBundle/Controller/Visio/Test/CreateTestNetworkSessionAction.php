<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Visio\Test;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CreateTestNetworkSessionAction
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        RouterInterface $router
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->router = $router;
    }

    /**
     * Page to create a session to test the video/audio/network quality of the user
     *
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse
     */
    public function __invoke(EventDomain $eventDomain): RedirectResponse
    {
        $sessionId = $this->videoConferenceAdapter->createSession();

        return new RedirectResponse($this->router->generate(
            'event_video_conference_network_test',
            ['sessionId' => $sessionId]
        ));
    }
}
