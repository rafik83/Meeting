<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MultipleSheets;

use Proximum\Vimeet\Application\Exception\MultipleSheets\Request\NoResultException;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\FilterRequestView;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetListViewQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MultipleSheet\Request\FilterRequestType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class RequestController extends Controller
{
    const PAGINATE_REQUEST_LIMIT = 50;

    /**
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param UserInterface $user
     *
     * @return Response
     */
    public function listAction(Request $request, EventDomain $eventDomain, UserInterface $user = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $event = $eventDomain->getEvent();

        $sheets = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetsByUserAndEvent($user, $event);

        $filterRequestView = new FilterRequestView();
        $form = $this->createForm(FilterRequestType::class, $filterRequestView, [
            'submit'             => true,
            'method'             => 'GET',
            'csrf_protection'    => false,
            'required'           => false,
            'allow_extra_fields' => true,
            'sheets'             => $sheets,
            'event'              => $eventDomain->getEvent(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filterRequestView = new FilterRequestView();
        }

        try {
            $sheetListView = $this->get('tactician.commandbus.query')->handle(
                new SheetListViewQuery(
                    $this->getUser(),
                    $sheets,
                    $request->getLocale(),
                    $request->get('page', 1),
                    self::PAGINATE_REQUEST_LIMIT,
                    $filterRequestView
                )
            );
        } catch (NoResultException $exception) {
            throw $this->createNotFoundException('Page not found');
        }

        return $this->render('EventBundle:MultipleSheets/Request:index.html.twig', [
            'event'         => $event,
            'filterForm'    => $form->createView(),
            'sheetListView' => $sheetListView,
            'isMultipleSheet' => true,
        ]);
    }
}
