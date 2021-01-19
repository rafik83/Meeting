<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Query\Tip\TipViewQuery;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListAction
{
    const TEMPLATE = 'AdminBundle:Tip:list.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBus */
    private $commandBus;

    /** @var EngineInterface */
    private $engine;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param CommandBus                           $commandBus
     * @param EngineInterface                      $engine
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBus $commandBus,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->engine = $engine;
    }

    /**
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Access denied');
        }

        $tipViewQuery = new TipViewQuery($request->query->get('page', 1));
        $tipListView = $this->commandBus->handle($tipViewQuery);

        return $this->engine->renderResponse(self::TEMPLATE, [
            'tipListView' => $tipListView,
        ]);
    }
}
