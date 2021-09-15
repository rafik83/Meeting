<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeAndPlanningByEventQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ShowAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        Environment $twig,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->twig = $twig;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        Event $event,
        User $user
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        return new Response(
            $this->twig->render(
                'AdminBundle:User/Event/Badge:show.html.twig',
                [
                    'event' => $event,
                    'userBadgeAndPlanningByEventView' => $this->queryBus->handle(
                        new GetUserBadgeAndPlanningByEventQuery($event, $user)
                    ),
                ]
            )
        );
    }
}
