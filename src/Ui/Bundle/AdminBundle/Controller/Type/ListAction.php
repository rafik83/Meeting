<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Type\TypeViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    const TEMPLATE = 'AdminBundle:Type:list.html.twig';

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
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $typeViewQuery = new TypeViewQuery(
            $request->query->get('page', 1),
            $event,
            $event->getAvailableLocale($request->getLocale())
        );

        $typeListsView = $this->queryBus->handle($typeViewQuery);

        return new Response($this->twig->render(self::TEMPLATE, [
            'event' => $event,
            'types' => $typeListsView,
        ]));
    }
}
