<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Order\AddRowToGroup;
use Proximum\Vimeet\Application\Command\Order\AddRowToProduct;
use Proximum\Vimeet\Application\Command\Order\ApplyPromotionCode;
use Proximum\Vimeet\Application\Command\Order\RemoveRow;
use Proximum\Vimeet\Application\Command\Order\UpdateRow;
use Proximum\Vimeet\Application\Query\Order\PaginatedOrderListViewQuery;
use Proximum\Vimeet\Application\Query\Order\SummaryQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\AddRowType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\FilterPartType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\FilterType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\UpdateRowType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\ApplyPromotionCodeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderController extends AbstractController
{
    private FlashBagInterface $flashBag;
    private TranslatorInterface $translator;
    private FormFactoryInterface $formFactory;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function listAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        if (null === $request->query->get('enabled')) {
            return $this->redirectToRoute('admin_sheet_order_list', array_merge(
                ['event' => $event->getId()],
                array_merge($request->query->all(), FilterType::getDefaultFilters())
            ));
        }

        $filters    = [];
        $filterForm = $this->createFilterForm(
            FilterType::class,
            $filters,
            ['event' => $event, 'locale' => $locale]
        );

        $filterPartForm = $this->createFilterForm(FilterPartType::class, $filters, [
            'event'  => $event,
            'locale' => $locale,
        ]);

        $filterPartForm->handleRequest(Request::create($request->getUri()));
        $filtered = $filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid();

        if ($filtered) {
            $filters = $filterForm->getData();
        }

        $query = new PaginatedOrderListViewQuery(
            $event,
            $filters,
            $request->query->getInt('page', 1),
            20,
            $locale
        );
        $orders = $this->queryBus->handle($query);

        return $this->render('AdminBundle:Order:list.html.twig', [
            'event'          => $event,
            'orders'         => $orders,
            'filterForm'     => $filterForm->createView(),
            'filterPartForm' => $filterPartForm->createView(),
        ]);
    }

    public function editAction(Request $request, Event $event, Order $order): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessIfOrderNotInEvent($event, $order);
        $this->denyAccessIfOrderIsInvoiced($order);

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetTitle($order->getSheet(), $event->getAvailableLocale($request->getLocale()))
        ;

        $summaryView = $this->queryBus->handle(
            new SummaryQuery(
                $order->getSheet(),
                $order,
                $event->getAvailableLocale($request->getLocale())
            )
        );

        $applyPromotionCode = new ApplyPromotionCode($order);

        $promotionCodeChoiceForm = $this->createForm(
            ApplyPromotionCodeType::class,
            $applyPromotionCode,
            ['event' => $event, 'submit' => true]
        );

        $promotionCodeChoiceForm->handleRequest($request);

        if ($promotionCodeChoiceForm->isSubmitted() && $promotionCodeChoiceForm->isValid()) {
            try {
                $this->commandBus->handle($applyPromotionCode);
                $this->flashBag->add('success', 'flash.admin.order.promotionCode.added');

                return $this->redirectToRoute(
                    'admin_sheet_order_edit',
                    ['event' => $event->getId(), 'order' => $order->getId()]
                );
            } catch (PromotionCodeException $exception) {
                $promotionCodeChoiceForm->get('promotionCode')->addError(
                    new FormError($this->translator->trans($exception->getFlash(), [], 'flashes'))
                );
            }
        }

        return $this->render('AdminBundle:Order:edit.html.twig', [
            'event' => $event,
            'sheet_info' => $sheetInfo,
            'order' => $order,
            'order_view' => $summaryView,
            'promotion_code_choice_form' => $promotionCodeChoiceForm->createView(),
        ]);
    }

    public function addRowToGroupAction(Request $request, Event $event, Order $order, string $group): Response
    {
        $this->denyAccessIfOrderNotInEvent($event, $order);
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetTitle($order->getSheet(), $event->getAvailableLocale($request->getLocale()))
        ;

        $addRow = new AddRowToGroup($order, $group);
        $form   = $this->createForm(AddRowType::class, $addRow);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($addRow);
            $this->addFlash('success', 'flash.admin.order.add_row.success');

            return $this->redirectToRoute('admin_sheet_order_edit', [
                'event' => $event->getId(),
                'order' => $order->getId(),
            ]);
        }

        return $this->render(
            'AdminBundle:Order:addRow.html.twig',
            [
                'event'      => $event,
                'sheet_info' => $sheetInfo,
                'order'      => $order,
                'form'       => $form->createView(),
            ]
        );
    }

    public function addRowToProductAction(Request $request, Event $event, Order $order, Order\Row $row): Response
    {
        $this->denyAccessIfOrderNotInEvent($event, $order);
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetTitle($order->getSheet(), $event->getAvailableLocale($request->getLocale()))
        ;

        $addRow = new AddRowToProduct($order, $row);
        $form   = $this->createForm(AddRowType::class, $addRow);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($addRow);
            $this->addFlash('success', 'flash.admin.order.add_row.success');

            return $this->redirectToRoute('admin_sheet_order_edit', [
                'event' => $event->getId(),
                'order' => $order->getId(),
            ]);
        }

        return $this->render(
            'AdminBundle:Order:addRow.html.twig',
            [
                'event'      => $event,
                'sheet_info' => $sheetInfo,
                'order'      => $order,
                'row_label'  => $row->getLabel($event->getAvailableLocale($request->getLocale())),
                'row'        => $row,
                'form'       => $form->createView(),
            ]
        );
    }

    public function updateRowAction(Request $request, Event $event, Order $order, Order\Row $row): Response
    {
        $this->denyAccessIfOrderNotInEvent($event, $order);
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetTitle($order->getSheet(), $event->getAvailableLocale($request->getLocale()));

        $updateRow = new UpdateRow($row);
        $form      = $this->createForm(UpdateRowType::class, $updateRow);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($updateRow);
            $this->addFlash('success', 'flash.admin.order.update_row.success');

            return $this->redirectToRoute('admin_sheet_order_edit', [
                'event' => $event->getId(),
                'order' => $order->getId(),
            ]);
        }

        return $this->render(
            'AdminBundle:Order:updateRow.html.twig',
            [
                'event'      => $event,
                'sheet_info' => $sheetInfo,
                'order'      => $order,
                'form'       => $form->createView(),
            ]
        );
    }

    public function removeRowAction(Event $event, Order $order, Order\Row $row): RedirectResponse
    {
        $this->denyAccessIfOrderNotInEvent($event, $order);
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $this->commandBus->handle(new RemoveRow($row));
        $this->addFlash('success', 'flash.admin.order.remove_row.success');

        return $this->redirectToRoute('admin_sheet_order_edit', [
            'event' => $event->getId(),
            'order' => $order->getId(),
        ]);
    }

    private function denyAccessIfOrderNotInEvent(Event $event, Order $order): void
    {
        if ($order->getSheet()->getEvent() !== $event) {
            throw $this->createAccessDeniedException();
        }
    }

    private function createFilterForm(string $type, array $data, array $options = []): FormInterface
    {
        return $this->formFactory->createNamed('', $type, $data, array_merge($options, [
            'method'             => 'GET',
            'csrf_protection'    => false,
            'required'           => false,
            'allow_extra_fields' => true,
        ]));
    }

    private function denyAccessIfOrderIsInvoiced(Order $order): void
    {
        if (null !== $order->getInvoice()) {
            throw $this->createAccessDeniedException();
        }
    }
}
