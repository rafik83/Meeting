<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Order;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Order\SummaryQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class SummaryTotalAction
{
    private const TEMPLATE = 'EventBundle:Order/SummaryTotal:summaryTotal.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var Merger */
    private $merger;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var Environment */
    private $twig;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        Merger $merger,
        QueryBusInterface $queryBus,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->merger = $merger;
        $this->queryBus = $queryBus;
        $this->twig = $twig;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function __invoke(Request $request, EventDomain $eventDomain, Sheet $sheet): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $orders = $sheet->getNotCancelledOrders();

        if (!$sheet->getPackage()->isPassable() || 0 === \count($orders)) {
            throw new NotFoundHttpException('This page is not accessible by this user');
        }

        $order = $this->merger->merge($orders);
        $view = $this->queryBus->handle(new SummaryQuery(
            $sheet,
            $order,
            $request->getLocale()
        ));

        return new Response($this->twig->render(self::TEMPLATE, [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'order' => $order,
            'view'  => $view,
        ]));
    }
}
