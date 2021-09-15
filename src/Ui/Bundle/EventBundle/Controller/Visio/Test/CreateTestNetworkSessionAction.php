<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Visio\Test;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreateTestNetworkSessionAction
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var RouterInterface */
    private $router;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        RouterInterface $router,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->router = $router;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    /**
     * Page to create a session to test the video/audio/network quality of the user
     *
     * @param EventDomain $eventDomain
     * @param Sheet|null  $sheet
     *
     * @return RedirectResponse
     */
    public function __invoke(
        EventDomain $eventDomain,
        ?Sheet $sheet = null
    ): RedirectResponse {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }

        $sessionId = $this->videoConferenceAdapter->createSession();

        if (null !== $sheet) {
            return new RedirectResponse($this->router->generate(
                'event_sheet_video_conference_network_test',
                [
                    'sheet' => $sheet->getId(),
                    'sessionId' => $sessionId,
                ]
            ));
        }

        return new RedirectResponse($this->router->generate(
            'event_video_conference_network_test',
            [
                'sessionId' => $sessionId
            ]
        ));
    }
}
