<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\User\ActivateAccount\SendActivateAccountFromLoginToken;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Security\LoginSecondStepAccessChecker;
use Proximum\Vimeet\Application\Query\User\UserImpersonateViewQuery;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOComexposiumViewQuery;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Security\CanPasswordBeDefinedWithActivationEmail;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Http\Firewall\SwitchSheetGroupManagerToSheetUser;
use Proximum\Vimeet\Infrastructure\Repository\Event\ExtraParameterRepository;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Model\Email;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Common\EmailType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Login\LoginType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Sheet\GroupVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private LoginSecondStepAccessChecker $loginSecondStepAccessChecker;
    private CanPasswordBeDefinedWithActivationEmail $canPasswordBeDefinedWithActivationEmail;
    private UserRepositoryInterface $userRepository;
    private TypeRepositoryInterface $typeRepository;
    private SheetRepositoryInterface $sheetRepository;
    private ExtraParameterRepository $eventExtraParameterRepository;
    private SwitchSheetGroupManagerToSheetUser $switchSheetGroupManagerToSheetUser;
    private AuthenticationUtils $authenticationUtils;
    private AuthenticationManagerInterface $authenticationManager;
    private TokenStorageInterface $tokenStorage;
    private MarkdownAdapterInterface $markdown;
    private TranslatorInterface $translator;
    private FlashBagInterface $flashBag;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        LoginSecondStepAccessChecker $loginSecondStepAccessChecker,
        CanPasswordBeDefinedWithActivationEmail $canPasswordBeDefinedWithActivationEmail,
        UserRepositoryInterface $userRepository,
        TypeRepositoryInterface $typeRepository,
        SheetRepositoryInterface $sheetRepository,
        ExtraParameterRepository $eventExtraParameterRepository,
        SwitchSheetGroupManagerToSheetUser $switchSheetGroupManagerToSheetUser,
        AuthenticationUtils $authenticationUtils,
        AuthenticationManagerInterface $authenticationManager,
        TokenStorageInterface $tokenStorage,
        MarkdownAdapterInterface $markdown,
        TranslatorInterface $translator,
        FlashBagInterface $flashBag,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->loginSecondStepAccessChecker = $loginSecondStepAccessChecker;
        $this->canPasswordBeDefinedWithActivationEmail = $canPasswordBeDefinedWithActivationEmail;
        $this->userRepository = $userRepository;
        $this->typeRepository = $typeRepository;
        $this->sheetRepository = $sheetRepository;
        $this->eventExtraParameterRepository = $eventExtraParameterRepository;
        $this->switchSheetGroupManagerToSheetUser = $switchSheetGroupManagerToSheetUser;
        $this->authenticationUtils = $authenticationUtils;
        $this->authenticationManager = $authenticationManager;
        $this->tokenStorage = $tokenStorage;
        $this->markdown = $markdown;
        $this->translator = $translator;
        $this->flashBag = $flashBag;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function loginFirstStepAction(
        Request $request,
        EventDomain $eventDomain,
        bool $isDebugMode,
        string $appEnv
    ): Response {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        $event = $eventDomain->getEvent();

        // Clean register type for potential next step
        $this->flashBag->get('register_type');

        $email = new Email();
        $form  = $this->createForm(EmailType::class, $email);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            // clear potential previous email before setting new one
            $this->flashBag->get('login_email');
            $this->addFlash('login_email', $email->email);
            if ($this->loginSecondStepAccessChecker->allowedToAccess($event, $email->email)) {

                if ($this->canPasswordBeDefinedWithActivationEmail->isSatisfiedBy($event, $email->email)) {
                    return $this->redirectToRoute('event_login_send_activation_mail');
                }

            }

            return $this->redirectToRoute('event_login_second_step');
        }

        $users = ($isDebugMode && $appEnv === 'dev' && 'true' === $request->get('oneClickLogin')) ?
            array_slice($this->userRepository->findWithEnabledSheetByEvent($event), 0, 50) :
            [];

        $hasError = 0 < count($form->getErrors(true)) || $this->flashBag->has('error');

        $ssoComexposiumView = !$hasError
            ? $this->queryBus->handle(new SSOComexposiumViewQuery($event, $request->getLocale()))
            : null;

        return $this->render('EventBundle:Security:login_first_step.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'users' => $users,
            'ssoComexposiumView' => $ssoComexposiumView,
        ]);
    }

    public function loginSecondStepAction(
        Request $request,
        EventDomain $eventDomain,
        DateTimeInterface $dateTime
    ): Response {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        $event = $eventDomain->getEvent();

        $typeFlashBag = $this->flashBag->get('register_type');
        $typeId       = array_shift($typeFlashBag);
        $type         = null;

        if (null !== $typeId) {
            if (\is_int($typeId) && $type = $this->typeRepository
                    ->getTypeViewById($typeId, $request->getLocale())
            ) {
                $this->addFlash('register_type', $typeId);
            } else {
                $typeId = null;
            }
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();

        $email = null;
        $emails = $this->flashBag->get('login_email');

        if (is_array($emails)) {
            if (empty($emails)) {
                $email = null;
            } else {
                $email = array_shift($emails);
            }
        }

        if (empty($email)) {
            return $this->redirectToRoute('event_login');
        } else if (!$this->loginSecondStepAccessChecker->allowedToAccess($event, $email)) {
            $user = null;
        } else {
            $user = $this->userRepository->findByEmail($email);

            if (null !== $user && $user->isTemporarilyDisabledDueToFailedAuthentication($dateTime)) {
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

        $eventExtraParam = $this->eventExtraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_TECH_EVENT_LOGIN_ENABLED
        );

        $actionUrl = $this->generateUrl('event_login_check');
        if ($eventExtraParam instanceof Event\ExtraParameter) {
            $actionUrl = $this->generateUrl(Route::TECH_EVENT_LOGIN_CHECK);
        }

        $form = $this->createForm(LoginType::class, ['username' => $email], [
            'action' => $actionUrl,
            'csrf_token_id' => 'authenticate'
        ]);

        if (null !== $error) {
            $form->get('password')->addError(new FormError($error->getMessage()));

            if ($error instanceof BadCredentialsException && null !== $user) {
                $remainingAuthenticationAttempt = $user->getRemainingAuthenticationAttempt($dateTime);

                $form->get('password')->addError(
                    new FormError(
                        $this->translator->transChoice(
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
            'typeDescription' => null !== $type ? $this->markdown->toHtml($type->description) : null,
        ]);
    }

    public function logoutConfirmationAction(Request $request, EventDomain $eventDomain): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        return $this->render('EventBundle:Security:logout_confirmation.html.twig', [
            'event'  => $eventDomain->getEvent(),
            'locale' => $request->getLocale(),
        ]);
    }

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

    public function sendActivationMailAction(EventDomain $eventDomain): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        $emails = $this->flashBag->get('login_email');
        $email = array_shift($emails);

        if (false === is_string($email) || false === $this->canPasswordBeDefinedWithActivationEmail
                ->isSatisfiedBy($eventDomain->getEvent(), $email)) {
            throw new AccessDeniedHttpException('Invalid access');
        }

        $this->addFlash('login_email', $email);

        $user = $this->userRepository->findByEmail($email);

        $sheets = $this->sheetRepository
            ->getAllSheetsByUserAndEvent($user, $eventDomain->getEvent());

        $sheet = current($sheets);

        $this->commandBus->handle(new SendActivateAccountFromLoginToken($sheet, $user));

        return $this->render(
            'EventBundle:Security:login_send_activation_mail.html.twig',
            [
                'event' => $eventDomain->getEvent(),
            ]
        );
    }

    public function loginUserAction(User $user): RedirectResponse
    {
        $this->authenticationManager->authenticate($user, 'main');

        return $this->redirectToRoute('event');
    }

    public function impersonatingUserAction(Event $event, ?Sheet $sheet = null): Response
    {
        $userImpersonateView = null;
        $token = $this->tokenStorage->getToken();

        if (null !== $token) {
            $roles = $token->getRoles();

            $exitRoute      = $sheet instanceof Sheet ? 'admin_sheet_details' : 'admin_sheets_group_list';
            $exitParameters = $sheet instanceof Sheet ?
                ['event' => $event->getId(), 'sheet' => $sheet->getId()] :
                ['event' => $event->getId()];

            foreach ($roles as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonatingUser = $role->getSource()->getUser();

                    $userImpersonateView = $this->queryBus->handle(
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

    public function switchSheetGroupManagerToSheetUserAction(
        Group $sheetGroup,
        UserInterface $user,
        Sheet $sheet
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(GroupVoter::MANAGE, $sheetGroup);

        $this->switchSheetGroupManagerToSheetUser->handle(
            $user,
            $sheet,
            $sheet->getOwner()
        );

        return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
    }

    public function unswitchSheetGroupManagerAction(EventDomain $eventDomain, Group $sheetGroup): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $this->switchSheetGroupManagerToSheetUser->unswitch();

        return $this->redirectToRoute('event_sheet_group_index', ['sheetGroup' => $sheetGroup->getId()]);
    }

    public function impersonatingSheetGroupManagerToSheetUserAction(Event $event, Sheet $sheet): Response
    {
        $sheetGroup = $sheet->getGroup();

        if (null === $sheetGroup) {
            throw $this->createAccessDeniedException('Given sheet not have group');
        }

        $userImpersonateView = null;

        $token = $this->tokenStorage->getToken();

        if (null !== $token) {
            $roles = $token->getRoles();

            foreach ($roles as $role) {
                if ($role instanceof SwitchUserRole) {
                    $userImpersonateView = $this->queryBus->handle(
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
