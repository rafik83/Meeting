<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\User\ForgottenPassword;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\User\UserDetailsViewQuery;
use Proximum\Vimeet\Application\Query\User\UserListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\UserEvent\Exception\UserEventMissingException;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\FilterSummary;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\FilterPartType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\FilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    private FormFactoryInterface $formFactory;
    private FilterSummary $filterSummary;
    private TranslatorInterface $translator;
    private EventUrlGeneratorInterface $urlGenerator;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        FormFactoryInterface $formFactory,
        FilterSummary $filterSummary,
        TranslatorInterface $translator,
        EventUrlGeneratorInterface $urlGenerator,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->formFactory = $formFactory;
        $this->filterSummary = $filterSummary;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function listAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        if (null === $request->query->get('participation')
            || !in_array($request->query->get('participation'), FilterType::getAllFilters())
        ) {
            return $this->redirectToRoute('admin_users', array_merge(
                ['event' => $event->getId()],
                FilterType::getDefaultFilters()
            ));
        }

        $filters = [];

        $filterType = $this->createFilterForm(FilterType::class, $filters, [
            'event'  => $event,
            'locale' => $locale,
            'user'   => $this->getUser(),
        ]);

        $filterPartForm = $this->createFilterForm(FilterPartType::class, $filters);

        $filterPartForm->handleRequest($request);
        $filtered = $filterType->handleRequest($request)->isSubmitted() && $filterType->isValid();

        if ($filtered) {
            $filters = $filterType->getData();
        }

        if (!isset($filters['type'])) {
            $filters['types'] = $this
                ->get('vimeet_infrastructure.repository.type_repository')
                ->getAllowedTypesByEvent($this->getUser(), $event);
        } else {
            $filters['types'] = [$filters['type']];
        }

        $paginatedResult = $this->queryBus->handle(
            new UserListViewQuery($event, $locale, $request->query->get('page', 1), $filters)
        );

        $filterFormView = $filterType->createView();

        return $this->render('AdminBundle:User:list.html.twig', [
            'event'            => $event,
            'paginatedResult'  => $paginatedResult,
            'filter_form'      => $filterFormView,
            'filter_part_form' => $filterPartForm->createView(),
            'filters_summary'  => $this->filterSummary->getFilters($filterFormView, $filters, $event, $locale),
        ]);
    }

    public function showAction(Request $request, Event $event, User $user): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $forgottenPassword = new ForgottenPassword($event, $user->getLocale(), true);
        $forgottenPassword->email = $user->getEmail();
        $forgottenPasswordForm = $this->createFormBuilder(
                [],
                [
                    'action' => $this->generateUrl(
                        'admin_users_details',
                        ['event' => $event->getId(), 'user' => $user->getId()]
                    ),
                    'method' => 'POST',
                ]
            )
            ->add('submit', SubmitType::class, ['label' => 'form.user.requestNewPasswordLink.submit'])
            ->getForm()
        ;

        if ($forgottenPasswordForm->handleRequest($request)->isSubmitted() && $forgottenPasswordForm->isValid()) {
            /** @var ForgottenPasswordToken $forgottenPasswordToken */
            $forgottenPasswordToken = $this->commandBus->handle($forgottenPassword);
            $this->addFlash(
                'success',
                $this->translator->trans(
                    'admin.user.requestedNewPasswordLink',
                    [
                        '%url%' => $this->urlGenerator->generateEventAbsoluteUrl(
                            $event,
                            'event_create_new_password',
                            [
                                'token' => $forgottenPasswordToken->getToken(),
                                '_locale' => $user->getLocale(),
                            ]
                        )
                    ]
                )
            );

            return $this->redirectToRoute(
                'admin_users_details',
                ['event' => $event->getId(), 'user' => $user->getId()]
            );
        }

        try {
            $view = $this
                ->get('query.user.user_details_view_query_handler')
                ->handle(new UserDetailsViewQuery($user, $event))
            ;
        } catch (UserEventMissingException $userEventMissingException) {
            throw $this->createNotFoundException($userEventMissingException->getMessage());
        } catch (SheetNotFoundException $sheetNotFoundException) {
            throw $this->createNotFoundException($sheetNotFoundException->getMessage());
        }

        return $this->render('AdminBundle:User:show.html.twig', [
            'event' => $view->event,
            'user' => $view->user,
            'userSheetList' => $view->userSheetView,
            'forgottenPasswordForm' => $forgottenPasswordForm->createView()
        ]);
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
}
