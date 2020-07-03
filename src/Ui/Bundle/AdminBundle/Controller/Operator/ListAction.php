<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Operator;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Operator\OperatorListViewQuery;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Operator\FilterType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
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

    public function __invoke(
        Request $request,
        AdminDomain $adminDomain
    ): Response {
        if (!$this->authorizationChecker->isGranted('ROLE_ORGANIZER')) {
            throw new AccessDeniedException('Access denied!');
        }

        $filters    = ['event' => $request->query->get('event')];
        $filterForm = $this->formFactory->createNamed('', FilterType::class, $filters, [
            'events' => $adminDomain->getAdmin()->getEvents(),
        ]);

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filters = $filterForm->getData();
        }

        $operatorList = $this->queryBus->handle(new OperatorListViewQuery($adminDomain->getAdmin(), $filters));

        return new Response($this->engine->render('AdminBundle:Operator:list.html.twig', [
            'operatorList' => $operatorList,
            'filter_form' => $filterForm->createView(),
            'filtered' => $filterForm->isSubmitted() && $filterForm->isValid(),
        ]));
    }
}
