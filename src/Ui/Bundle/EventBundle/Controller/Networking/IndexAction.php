<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Networking;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Participant\UpdateNetworkingChatViewedAt;
use Proximum\Vimeet\Application\Query\Networking\NetworkingQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class IndexAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var NetworkingAccessChecker */
    private $networkingAccessChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EventOpenAccessChecker $eventOpenAccessChecker,
        NetworkingAccessChecker $networkingAccessChecker,
        CommandBusInterface $commandBus
    ) {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->networkingAccessChecker = $networkingAccessChecker;
        $this->commandBus = $commandBus;
    }

    public function __invoke(
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        Request $request
    ) {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->networkingAccessChecker->allowedToAccess($event)) {
            throw new AccessDeniedException();
        }

        $networkingView = $this->queryBus->handle(new NetworkingQuery($sheet, $user));

        $this->commandBus->handle(new UpdateNetworkingChatViewedAt($sheet, $user));

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_NETWORKING,
            $request->getLocale()
        );
        $tipTranslationViews = $this->queryBus->handle($tipTranslationViewQuery);

        return new Response(
            $this->engine->render(
                '@Event/Networking/index.html.twig',
                [
                    'networkingView' => $networkingView,
                    'sheet' => $sheet,
                    'event' => $event,
                    'currentUser' => $user,
                    'isEventOpen' => $this->eventOpenAccessChecker->allowedToAccess($event),
                    'tipTranslationViews' => $tipTranslationViews,
                ]
            )
        );
    }
}
