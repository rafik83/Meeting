<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Query\Tip\Event\PaginatedTipViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListAction
{
    const TEMPLATE = 'AdminBundle:Tip:Event/list.html.twig';

    /** @var CommandBus */
    private $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EngineInterface */
    private $engine;

    /**
     * @param CommandBus                           $commandBus
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param EngineInterface                      $engine
     */
    public function __construct(
        CommandBus $commandBus,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EngineInterface $engine
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->engine = $engine;
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $tipListViewQuery = new PaginatedTipViewQuery($event, $request->query->get('page', 1));
        $tipListView      = $this->commandBus->handle($tipListViewQuery);

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event'       => $event,
            'tipListView' => $tipListView,
            'locale'      => $event->getAvailableLocale($request->getLocale()),
        ]);
    }
}
