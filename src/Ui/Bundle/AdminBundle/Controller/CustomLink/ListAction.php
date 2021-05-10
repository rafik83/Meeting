<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\CustomLink;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\CustomLink\CustomLinkListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    private Environment $twig;

    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;

    private QueryBusInterface $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        Environment $twig
    ) {
        $this->twig = $twig;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException();
        }

        $list = $this->queryBus->handle(new CustomLinkListViewQuery($event, $request->getLocale()));

        return new Response(
            $this->twig->render(
                'AdminBundle:CustomLink:list.html.twig',
                [
                    'event' => $event,
                    'list' => $list,
                ]
            )
        );
    }
}
