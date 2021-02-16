<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\AvailabilityTimeRange;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\ListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListAction
{
    private const TEMPLATE = 'AdminBundle:AvailabilityTimeRange:list.html.twig';

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        QueryBusInterface $queryBus,
        EngineInterface $engine
    ) {
        $this->queryBus = $queryBus;
        $this->engine = $engine;
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

        $list = $this->queryBus->handle(new ListViewQuery($event));

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event' => $event,
            'list' => $list,
        ]);
    }
}
