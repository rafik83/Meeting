<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Networking;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Networking\PrivateChatQuery;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class PrivateChatAction
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
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        User $toUser
    )
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();

        if (!$this->networkingAccessChecker->allowedToAccess($event)) {
            throw new AccessDeniedException();
        }

        $chatView = $this->queryBus->handle(new PrivateChatQuery($event, $userDomain->getUser(), $toUser));

        return new Response(
            $this->engine->render(
                '@Event/Networking/privateChat.html.twig',
                [
                    'privateChatView' => $chatView,
                    'sheet' => $sheet,
                    'event' => $event,
                ]
            )
        );
    }
}
