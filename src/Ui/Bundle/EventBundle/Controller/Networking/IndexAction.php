<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Networking;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Networking\NetworkingQuery;
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

    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EventOpenAccessChecker $eventOpenAccessChecker,
        NetworkingAccessChecker $networkingAccessChecker
    )
    {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->networkingAccessChecker = $networkingAccessChecker;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    )
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            throw new AccessDeniedException();
        }

        $participants = $sheet->getParticipants();
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->networkingAccessChecker->allowedToAccess($event)) {
            throw new AccessDeniedException();
        }

        $networkingView = $this->queryBus->handle(new NetworkingQuery($event, $user));

        return new Response(
            $this->engine->render(
                '@Event/Networking/index.html.twig',
                [
                    'networkingView' => $networkingView,
                    'participant' => $participants,
                    'sheet' => $sheet,
                    'event' => $event,
                    'isEventOpen' => $this->eventOpenAccessChecker->allowedToAccess($event),
                ]
            )
        );
    }
}
