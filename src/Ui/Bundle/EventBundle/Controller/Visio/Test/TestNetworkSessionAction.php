<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Visio\Test;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\VideoConference\RequestTestAccess;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class TestNetworkSessionAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var Environment */
    private $twig;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        CommandBusInterface $commandBus,
        Environment $twig,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->commandBus = $commandBus;
        $this->twig = $twig;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        string $sessionId,
        ?Sheet $sheet = null
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }
        $event = $eventDomain->getEvent();

        try {
            /** @var VideoConferenceView $videoConferenceView */
            $videoConferenceView = $this->commandBus->handle(
                new RequestTestAccess(
                    $event,
                    $sessionId,
                    $event->getAvailableLocale($request->getLocale())
                )
            );
        } catch (InvalidTokenGeneratorArgumentsException $exception) {
            throw new NotFoundHttpException('The sessionId is not valid');
        }

        return new Response(
            $this->twig->render(
                'EventBundle:VideoConference:testNetworkAudioVideo.html.twig',
                [
                    'sheet' => $sheet,
                    'event' => $event,
                    'videoConferenceView' => $videoConferenceView,
                ]
            )
        );
    }
}
