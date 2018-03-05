<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\User\UserImpersonateViewQuery;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOComexposiumViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
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

        // Clean register type for potential next step
        $this->get('session')->getFlashBag()->get('register_type');

        $email = new Email();
        $form  = $this->createForm(EmailType::class, $email);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            if ($this->get('vimeet_infrastructure.repository.user_repository')->emailExists($email->email)) {
                // clear potential previous email before setting new one
                $this->get('session')->getFlashBag()->get('login_email');
                $this->addFlash('login_email', $email->email);

                return $this->redirectToRoute('event_login_second_step');
            }

            $error = new FormError($this->get('translator')->trans(
                'validators.login.email_not_exists',
                [],
                'validators',
                $request->getLocale()
            ));

            $form->get('email')->addError($error);
        }

        $users = $this->get('kernel')->getEnvironment() === 'dev' ?
            $this->get('vimeet_infrastructure.repository.user_repository')->all() :
            [];

        $hasError = 0 < \count($form->getErrors(true)) || $this->get('session')->getFlashBag()->has('error');

        $ssoComexposiumView = !$hasError
            ? $this->get(QueryBus::class)->handle(
                new SSOComexposiumViewQuery(
                    $eventDomain->getEvent(),
                    $request->getLocale(),
                    null,
                    false
                )
            )
            : null;

        return $this->render('EventBundle:Security:login_first_step.html.twig', [
            'event' => $eventDomain->getEvent(),
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
        $error               = $authenticationUtils->getLastAuthenticationError();

        $email = $this->get('session')->getFlashBag()->get('login_email');

        if (empty($email) || null === ($email = array_shift($email))
            || !$this->get('vimeet_infrastructure.repository.user_repository')->emailExists($email)
        ) {
            return $this->redirectToRoute('event_login');
        }

        $this->addFlash('login_email', $email);

        $form = $this->createForm(LoginType::class, ['username' => $email], [
            'action' => $this->generateUrl('event_login_check'),
        ]);

        if (null !== $error) {
            $form->get('password')->addError(new FormError($error->getMessage()));
        }

        $ssoComexposiumView = $this->get(QueryBus::class)->handle(
            new SSOComexposiumViewQuery(
                $eventDomain->getEvent(),
                $request->getLocale(),
                $email,
                true
            )
        );

        return $this->render('EventBundle:Security:login_second_step.html.twig', [
            'event'    => $eventDomain->getEvent(),
            'form'     => $form->createView(),
            'username' => $email,
            'error'    => $error,
            'typeId'   => $typeId,
            'type'     => $type,
            'ssoComexposiumView' => $ssoComexposiumView,
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
    public function logoutSuccessAction(Request $request, EventDomain $eventDomain)
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
     * @param Event $event
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
            'sheetGroup'        => $sheetGroup
        ]);
    }
}
