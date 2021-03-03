<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\LinkedSheets;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin\LinkedSheetsListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    private const TEMPLATE = 'AdminBundle:LinkedSheets:list.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var Environment */
    private $twig;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->twig = $twig;
    }

    public function __invoke(Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $linkedSheetsListView = $this->queryBus->handle(new LinkedSheetsListViewQuery($event));

        return new Response($this->twig->render(
            self::TEMPLATE,
            [
                'event'                => $event,
                'linkedSheetsListView' => $linkedSheetsListView,
            ]
        ));
    }
}
