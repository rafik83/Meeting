<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Components\Sheet\Details\Invoice\InvoiceViewQuery;
use Proximum\Vimeet\Application\Query\Order\ProFormaQuery;
use Proximum\Vimeet\Application\Query\Order\SummaryQuery;
use Proximum\Vimeet\Application\Query\Payment\PaymentConditionsViewQuery;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function listAction(EventDomain $eventDomain, Sheet $sheet): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $balance = $this->get('order.balance');
        $orderVatViews = $balance->getOrderVatViews($sheet);

        if (!$sheet->getPackage()->isPassable() || empty($orderVatViews)) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        $paymentConditionsView = $this
            ->get('Proximum\Vimeet\Infrastructure\Adapter\QueryBus')
            ->handle(new PaymentConditionsViewQuery($sheet))
        ;

        $canPayIfRemaining = \in_array(Mode::PAYMENT_PAYPAL, $paymentConditionsView->paymentModes, true);

        return $this->render('EventBundle:Order:list.html.twig', [
            'event'             => $eventDomain->getEvent(),
            'canPayIfRemaining' => $canPayIfRemaining,
            'orderVatViews'     => $orderVatViews,
            'remainingToPay'    => $balance->getRemainingToPay($sheet),
            'sheet'             => $sheet,
            'transactions'      => $balance->getTransactions($sheet),
            'invoiceViews'      => $this->get('sheet.sheet_details.invoice_view_query_handler')->handle(
                new InvoiceViewQuery($sheet)
            ),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Order       $order
     *
     * @return Response
     */
    public function proFormaAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Order $order): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if ($order->getSheet() !== $sheet || !$sheet->getPackage()->isPassable()) {
            throw $this->createNotFoundException('This page is not accessible by this user');
        }

        $view = $this->get('tactician.commandbus.query')->handle(
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

        $order = $this->get('order.merger')->merge($orders);

        $view = $this->get('tactician.commandbus.query')->handle(new SummaryQuery(
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
