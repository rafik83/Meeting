<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Admin;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Admin\AdminListViewQuery;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\FilterAdminType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ListAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Access denied!');
        }

        $filters = [];
        $filtered = false;
        $filterForm = $this->formFactory->createNamed(
            '',
            FilterAdminType::class,
            [
                'role'  => $request->query->get('role'),
                'event' => $request->query->get('event'),
            ]
        );

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filters   = $filterForm->getData();
            $filtered = true;
        }

        $adminList = $this->queryBus->handle(new AdminListViewQuery($filters));

        return new Response($this->engine->render('AdminBundle:Admin:list.html.twig', [
            'adminList' => $adminList,
            'filter_form' => $filterForm->createView(),
            'filtered' => $filtered,
        ]));
    }
}
