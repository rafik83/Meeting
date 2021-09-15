<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MeetingRequest;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Exception\MultipleSheets\Request\NoResultException;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\FilterRequestView;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetListViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\MultipleSheet\Request\FilterRequestType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class MergedListAction
{
    private const RESULTS_PER_PAGE = 50;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        Environment $twig,
        FormFactoryInterface $formFactory,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    ): Response {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        $sheets = [$sheet->getId() => $sheet];
        $isMultipleSheet = false;

        $linkedSheets = $sheet->getLinkedSheets();

        if (null !== $linkedSheets) {
            $isMultipleSheet = true;

            foreach ($linkedSheets->getSheets() as $linkedSheet) {
                $sheets[$linkedSheet->getId()] = $linkedSheet;
            }
        }

        $filterRequestView = new FilterRequestView();
        $form = $this->formFactory->create(FilterRequestType::class, $filterRequestView, [
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
                    $user,
                    $sheets,
                    $request->getLocale(),
                    $request->get('page', 1),
                    self::RESULTS_PER_PAGE,
                    $filterRequestView
                )
            );
        } catch (NoResultException $exception) {
            throw new NotFoundHttpException('Page not found');
        }

        return new Response(
            $this->twig->render(
                '@Event/MeetingRequestMergedList/list.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'filterForm'    => $form->createView(),
                    'sheetListView' => $sheetListView,
                    'isMultipleSheet' => $isMultipleSheet,
                ]
            )
        );
    }
}
