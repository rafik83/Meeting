<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Query\Agenda\Admin\AgendaSheetViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\AgendaSpotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQuery;
use Proximum\Vimeet\Application\Query\Spot\Agenda\ListViewQuery;
use Proximum\Vimeet\Application\View\Spot\Agenda\ListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response|JsonResponse
     */
    public function indexAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        return $this->render('AdminBundle:Agenda:index.html.twig', [
            'event'                        => $event,
            'isMeetingRequestUpdateLocked' => $event->getConfiguration()->isMeetingRequestUpdateLocked(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return JsonResponse
     */
    public function sheetsListAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $sheets = $this->get('tactician.commandbus.query')->handle(
            new SheetListViewQuery($event, $event->getAvailableLocale($request->getLocale()), false)
        );

        return new JsonResponse($sheets);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $sheet
     *
     * @return JsonResponse
     */
    public function sheetAction(Request $request, Event $event, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($sheet->getEvent() !== $event) {
            return new JsonResponse('Sheet are not on this event', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $agendaSheetView = $this->get('tactician.commandbus.query')->handle(
            new AgendaSheetViewQuery($sheet, $event->getAvailableLocale($request->getLocale()))
        );

        return new JsonResponse($agendaSheetView);
    }

    /**
     * @param Event $event
     *
     * @return JsonResponse
     */
    public function spotsListAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        /** @var ListView $spots */
        $listView = $this->get('tactician.commandbus.query')->handle(
            new ListViewQuery($event)
        );

        return new JsonResponse($listView->spotViews);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Spot    $spot
     *
     * @return JsonResponse
     */
    public function spotDetailAction(Request $request, Event $event, Spot $spot)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $agendaSpotView = $this->get('tactician.commandbus.query')->handle(
            new AgendaSpotViewQuery($spot, $event, $event->getAvailableLocale($request->getLocale()))
        );

        return new JsonResponse($agendaSpotView);
    }
}
