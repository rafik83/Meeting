<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\User\ForgottenPassword;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\User\UserDetailsViewQuery;
use Proximum\Vimeet\Application\Query\User\UserListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\UserEvent\Exception\UserEventMissingException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\FilterPartType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\FilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
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

        $paginatedResult = $this->get('tactician.commandbus.query')->handle(
            new UserListViewQuery($event, $locale, $request->query->get('page', 1), $filters)
        );

        $filterFormView = $filterType->createView();

        return $this->render('AdminBundle:User:list.html.twig', [
            'event'            => $event,
            'paginatedResult'  => $paginatedResult,
            'filter_form'      => $filterFormView,
            'filter_part_form' => $filterPartForm->createView(),
            'filters_summary'  => $this->get('filter_summary')->getFilters($filterFormView, $filters, $event, $locale),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param User    $user
     *
     * @return Response
     */
    public function showAction(Request $request, Event $event, User $user)
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
            $forgottenPasswordToken = $this->get('tactician.commandbus')->handle($forgottenPassword);
            $this->addFlash(
                'success',
                $this->get('translator')->trans(
                    'admin.user.requestedNewPasswordLink',
                    [
                        '%url%' => $this->get('adapter.event_url_generator')->generateEventAbsoluteUrl(
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

    /**
     * @param string $type
     * @param array  $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, array_merge($options, [
            'method'             => 'GET',
            'csrf_protection'    => false,
            'required'           => false,
            'allow_extra_fields' => true,
        ]));
    }
}
