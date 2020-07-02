<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\User\ActivateAccount\SendActivateAccountFromLoginToken;
use Proximum\Vimeet\Application\Components\Security\LoginSecondStepAccessChecker;
use Proximum\Vimeet\Application\Query\User\UserImpersonateViewQuery;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOComexposiumViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\User\Security\CanPasswordBeDefinedWithActivationEmail;
use Proximum\Vimeet\Infrastructure\Adapter\QueryBus;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Model\Email;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Common\EmailType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Login\LoginType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response|RedirectResponse
     */
    public function loginFirstStepAction(Request $request, EventDomain $eventDomain)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        $event = $eventDomain->getEvent();

        // Clean register type for potential next step
        $this->get('session')->getFlashBag()->get('register_type');

        $email = new Email();
        $form  = $this->createForm(EmailType::class, $email);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            // clear potential previous email before setting new one
            $this->get('session')->getFlashBag()->get('login_email');
            $this->addFlash('login_email', $email->email);
            if ($this->get(LoginSecondStepAccessChecker::class)->allowedToAccess($event, $email->email)) {

                if ($this->get(CanPasswordBeDefinedWithActivationEmail::class)->isSatisfiedBy($event, $email->email)) {
                    return $this->redirectToRoute('event_login_send_activation_mail');
                }

            }

            return $this->redirectToRoute('event_login_second_step');
        }

        $users = 'dev' === $this->get('kernel')->getEnvironment() && 'true' === $request->get('oneClickLogin') ?
            $this->get('vimeet_infrastructure.repository.user_repository')->all() :
            [];

        $hasError = 0 < count($form->getErrors(true)) || $this->get('session')->getFlashBag()->has('error');

        $ssoComexposiumView = !$hasError
            ? $this->get(QueryBus::class)->handle(new SSOComexposiumViewQuery($event, $request->getLocale()))
            : null;

        return $this->render('EventBundle:Security:login_first_step.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'users' => $users,
            'ssoComexposiumView' => $ssoComexposiumView,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response|RedirectResponse
     */
    public function loginSecondStepAction(Request $request, EventDomain $eventDomain)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        $event = $eventDomain->getEvent();

        $now = $this->get('datetime');
        $typeFlashBag = $this->get('session')->getFlashBag()->get('register_type');
        $typeId       = array_shift($typeFlashBag);
        $type         = null;

        if (null !== $typeId) {
            if (\is_int($typeId) && $type = $this->get('vimeet_infrastructure.repository.type_repository')
                    ->getTypeViewById($typeId, $request->getLocale())
            ) {
                $this->addFlash('register_type', $typeId);
            } else {
                $typeId = null;
            }
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();

        $email = $this->get('session')->getFlashBag()->get('login_email');

        if (empty($email) || null === ($email = array_shift($email))
            || !$this->get(LoginSecondStepAccessChecker::class)->allowedToAccess($event, $email)
        ) {
            $user = null;
        } else {
            $user = $this->get('vimeet_infrastructure.repository.user_repository')->findByEmail($email);

            if (null !== $user && $user->isTemporarilyDisabledDueToFailedAuthentication($now)) {
                return $this->render(
                    '@Event/Security/account_temporarily_disabled.html.twig', [
                    'event' => $event,
                    'username' => $email,
                    'typeId' => $typeId,
                    'type' => $type,
                ]);
            }
        }

        $this->addFlash('login_email', $email);

        $form = $this->createForm(LoginType::class, ['username' => $email], [
            'action' => $this->generateUrl('event_login_check'),
        ]);

        if (null !== $error) {
            $form->get('password')->addError(new FormError($error->getMessage()));

            if ($error instanceof BadCredentialsException && null !== $user) {
                $remainingAuthenticationAttempt = $user->getRemainingAuthenticationAttempt($now);

                $form->get('password')->addError(
                    new FormError(
                        $this->get('translator')->transChoice(
                            'authentication.remaining_attempt',
                            $remainingAuthenticationAttempt,
                            ['%remainingAttempt%' => $remainingAuthenticationAttempt]
                        )
                    )
                );
            }
        }

        return $this->render('EventBundle:Security:login_second_step.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'username' => $email,
            'error' => $error,
            'typeId' => $typeId,
            'type' => $type,
            'typeDescription' => null !== $type ? $this->get('markdown')->toHtml($type->description) : null,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response|RedirectResponse
     */
    public function logoutConfirmationAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        return $this->render('EventBundle:Security:logout_confirmation.html.twig', [
            'event'  => $eventDomain->getEvent(),
            'locale' => $request->getLocale(),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response|RedirectResponse
     */
    public function logoutSuccessAction(Request $request, EventDomain $eventDomain): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event_logout_confirmation');
        }

        return $this->render('EventBundle:Security:logout_success.html.twig', [
            'event'  => $eventDomain->getEvent(),
            'locale' => $request->getLocale(),
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse|Response
     */
    public function sendActivationMailAction(EventDomain $eventDomain)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        $emails = $this->get('session')->getFlashBag()->get('login_email');
        $email = array_shift($emails);

        if (false === is_string($email) || false === $this->get(CanPasswordBeDefinedWithActivationEmail::class)
                ->isSatisfiedBy($eventDomain->getEvent(), $email)) {
            throw new AccessDeniedHttpException('Invalid access');
        }

        $this->addFlash('login_email', $email);

        $user = $this->get('vimeet_infrastructure.repository.user_repository')->findByEmail($email);

        $sheets = $this->get('vimeet_infrastructure.repository.sheet_repository')
            ->getAllSheetsByUserAndEvent($user, $eventDomain->getEvent());

        $sheet = current($sheets);

        $this->get('tactician.commandbus')->handle(new SendActivateAccountFromLoginToken($sheet, $user));

        return $this->render(
            'EventBundle:Security:login_send_activation_mail.html.twig',
            [
                'event' => $eventDomain->getEvent(),
            ]
        );
    }

    /**
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function loginUserAction(User $user)
    {
        $this->get('adapter.authentication_manager')->authenticate($user, 'main');

        return $this->redirectToRoute('event');
    }

    /**
     * @param Event      $event
     * @param null|Sheet $sheet
     *
     * @return Response
     */
    public function impersonatingUserAction(Event $event, $sheet = null)
    {
        $userImpersonateView = null;
        $token = $this->get('security.token_storage')->getToken();

        if (null !== $token) {
            $roles = $token->getRoles();

            $exitRoute      = $sheet instanceof Sheet ? 'admin_sheet_details' : 'admin_sheets_group_list';
            $exitParameters = $sheet instanceof Sheet ?
                ['event' => $event->getId(), 'sheet' => $sheet->getId()] :
                ['event' => $event->getId()];

            foreach ($roles as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonatingUser = $role->getSource()->getUser();

                    $userImpersonateView = $this->get('tactician.commandbus.query')->handle(
                        new UserImpersonateViewQuery(
                            $impersonatingUser,
                            $token->getUser(),
                            $exitRoute,
                            $exitParameters
                        )
                    );
                    break;
                }
            }
        }

        return $this->render('EventBundle:Security:impersonating.html.twig', [
            'event'           => $event,
            'sheet'           => $sheet,
            'impersonateView' => $userImpersonateView,
        ]);
    }

    /**
     * @param EventDomain   $eventDomain
     * @param Group         $sheetGroup
     * @param UserInterface $user
     * @param Sheet         $sheet
     *
     * @return RedirectResponse
     */
    public function switchSheetGroupManagerToSheetUserAction(
        EventDomain $eventDomain,
        Group $sheetGroup,
        UserInterface $user,
        Sheet $sheet
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $this->get('security.authentication.switch_sheet_group_manager_to_sheet_user')->handle(
            $user,
            $sheet,
            $sheet->getOwner()
        );

        return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
    }

    /**
     * @param EventDomain   $eventDomain
     * @param Group         $sheetGroup
     * @param UserInterface $user
     *
     * @return RedirectResponse
     */
    public function unswitchSheetGroupManagerAction(EventDomain $eventDomain, Group $sheetGroup, UserInterface $user)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $this->get('security.authentication.switch_sheet_group_manager_to_sheet_user')->unswitch();

        return $this->redirectToRoute('event_sheet_group_index', ['sheetGroup' => $sheetGroup->getId()]);
    }

    /**
     * @param Event         $event
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return Response
     */
    public function impersonatingSheetGroupManagerToSheetUserAction(Event $event, Sheet $sheet, UserInterface $user)
    {
        $sheetGroup = $sheet->getGroup();

        if (null === $sheetGroup) {
            throw $this->createAccessDeniedException('Given sheet not have group');
        }

        $userImpersonateView = null;

        $token = $this->get('security.token_storage')->getToken();

        if (null !== $token) {
            $roles = $token->getRoles();

            foreach ($roles as $role) {
                if ($role instanceof SwitchUserRole) {
                    $userImpersonateView = $this->get('tactician.commandbus.query')->handle(
                        new UserImpersonateViewQuery(
                            $role->getSource()->getUser(),
                            $token->getUser(),
                            'event_sheet_group_unswitch',
                            ['sheetGroup' => $sheetGroup->getId()]
                        )
                    );
                    break;
                }
            }
        }

        return $this->render('EventBundle:Security:impersonatingSheetGroupManagerToSheetUser.html.twig', [
            'impersonateView'   => $userImpersonateView,
            'event'             => $event,
            'sheetGroup'        => $sheetGroup,
        ]);
    }
}
