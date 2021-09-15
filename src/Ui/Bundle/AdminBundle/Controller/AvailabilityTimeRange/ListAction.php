<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\AvailabilityTimeRange;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\ListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    private const TEMPLATE = 'AdminBundle:AvailabilityTimeRange:list.html.twig';

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var Environment */
    private $twig;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        QueryBusInterface $queryBus,
        Environment $twig
    ) {
        $this->queryBus = $queryBus;
        $this->twig = $twig;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Event $event
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function __invoke(Event $event): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            throw new AccessDeniedException('Only Admin and organizer can access this page');
        }

        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException();
        }

        $list = $this->queryBus->handle(new ListViewQuery($event));

        return new Response($this->twig->render(self::TEMPLATE, [
            'event' => $event,
            'list' => $list,
        ]));
    }
}
