<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaSheetViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\AgendaSpotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQuery;
use Proximum\Vimeet\Application\Query\Spot\Agenda\ListViewQuery;
use Proximum\Vimeet\Application\View\Spot\Agenda\ListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends AbstractController
{
    private QueryBusInterface $queryBus;

    public function __construct(
        QueryBusInterface $queryBus
    ) {
        $this->queryBus = $queryBus;
    }

    public function indexAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        return $this->render('AdminBundle:Agenda:index.html.twig', [
            'event'                        => $event,
            'isMeetingRequestUpdateLocked' => $event->getConfiguration()->isMeetingRequestUpdateLocked(),
        ]);
    }

    public function sheetsListAction(Request $request, Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $sheets = $this->queryBus->handle(
            new SheetListViewQuery($event, $event->getAvailableLocale($request->getLocale()), false)
        );

        return new JsonResponse($sheets);
    }

    public function sheetAction(Request $request, Event $event, Sheet $sheet): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($sheet->getEvent() !== $event) {
            return new JsonResponse('Sheet are not on this event', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $agendaSheetView = $this->queryBus->handle(
            new AgendaSheetViewQuery($sheet, $event->getAvailableLocale($request->getLocale()))
        );

        return new JsonResponse($agendaSheetView);
    }

    public function spotsListAction(Event $event): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        /** @var ListView $spots */
        $listView = $this->queryBus->handle(
            new ListViewQuery($event)
        );

        return new JsonResponse($listView->spotViews);
    }

    public function spotDetailAction(Request $request, Event $event, Spot $spot): JsonResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $agendaSpotView = $this->queryBus->handle(
            new AgendaSpotViewQuery($spot, $event, $event->getAvailableLocale($request->getLocale()))
        );

        return new JsonResponse($agendaSpotView);
    }
}
