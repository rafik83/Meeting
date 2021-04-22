<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Group;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Exception\MultipleSheets\Request\NoResultException;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\FilterRequestView;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetListViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MultipleSheet\Request\FilterRequestType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestController extends AbstractController
{
    const PAGINATE_REQUEST_LIMIT = 50;

    private SheetRepositoryInterface $sheetRepository;
    private QueryBusInterface $queryBus;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        QueryBusInterface $queryBus
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->queryBus = $queryBus;
    }

    public function listAction(Request $request, EventDomain $eventDomain, Group $sheetGroup): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $sheets = $this->sheetRepository->getByGroup($sheetGroup);
        $filterRequestView = new FilterRequestView();
        $form = $this->createForm(FilterRequestType::class, $filterRequestView, [
            'submit'             => true,
            'method'             => 'GET',
            'csrf_protection'    => false,
            'required'           => false,
            'allow_extra_fields' => true,
            'event'              => $eventDomain->getEvent(),
            'sheets'             => $sheets,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filterRequestView = new FilterRequestView();
        }

        try {
            $sheetListView = $this->queryBus->handle(
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

        return $this->render('EventBundle:Sheet/Group/Request:index.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'filterForm'    => $form->createView(),
            'sheetGroup'    => $sheetGroup,
            'sheetListView' => $sheetListView,
            'isMultipleSheet' => false,
        ]);
    }
}
