<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Sheet\Details\Invoice\InvoiceViewQuery;
use Proximum\Vimeet\Application\Components\Sheet\Details\Invoice\InvoiceViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\ProFormaQuery;
use Proximum\Vimeet\Application\Query\Order\SummaryQuery;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQuery;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends AbstractController
{
    private Balance $orderBalance;
    private InvoiceViewQueryHandler $invoiceViewQueryHandler;
    private Merger $orderMerger;
    private QueryBusInterface $queryBus;

    public function __construct(
        Balance $orderBalance,
        InvoiceViewQueryHandler $invoiceViewQueryHandler,
        Merger $orderMerger,
        QueryBusInterface $queryBus
    ) {
        $this->orderBalance = $orderBalance;
        $this->invoiceViewQueryHandler = $invoiceViewQueryHandler;
        $this->orderMerger = $orderMerger;
        $this->queryBus = $queryBus;
    }

    public function listAction(EventDomain $eventDomain, Sheet $sheet): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $orderVatViews = $this->orderBalance->getOrderVatViews($sheet);

        if (!$sheet->getPackage()->isPassable() || empty($orderVatViews)) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        $paymentConditionsView = $this->queryBus->handle(new PaymentConditionsViewQuery($sheet))
        ;

        $canPayIfRemaining = \in_array(Mode::PAYMENT_PAYPAL, $paymentConditionsView->paymentModes, true);

        return $this->render('EventBundle:Order:list.html.twig', [
            'event'             => $eventDomain->getEvent(),
            'canPayIfRemaining' => $canPayIfRemaining,
            'orderVatViews'     => $orderVatViews,
            'remainingToPay'    => $this->orderBalance->getRemainingToPay($sheet),
            'sheet'             => $sheet,
            'transactions'      => $this->orderBalance->getTransactions($sheet),
            'invoiceViews'      => $this->invoiceViewQueryHandler->handle(
                new InvoiceViewQuery($sheet)
            ),
        ]);
    }

    public function proFormaAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Order $order): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if ($order->getSheet() !== $sheet || !$sheet->getPackage()->isPassable()) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        $view = $this->queryBus->handle(
            new ProFormaQuery(
                $sheet,
                $order,
                $request->getLocale()
            )
        );

        return $this->render('EventBundle:Order:pro_forma.html.twig', [
            'event'     => $eventDomain->getEvent(),
            'pro_forma' => $view,
            'sheet'     => $sheet,
            'order'     => $order,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function summaryTotalAction(Request $request, EventDomain $eventDomain, Sheet $sheet): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $orders = $sheet->getNotCancelledOrders();

        if (!$sheet->getPackage()->isPassable() || 0 === \count($orders)) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        $order = $this->orderMerger->merge($orders);

        $view = $this->queryBus->handle(new SummaryQuery(
            $sheet,
            $order,
            $request->getLocale()
        ));

        return $this->render('EventBundle:Order/SummaryTotal:summaryTotal.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'order' => $order,
            'view'  => $view,
        ]);
    }
}
