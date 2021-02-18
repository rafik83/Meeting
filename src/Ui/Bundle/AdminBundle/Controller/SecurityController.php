<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\AuthenticationManager;
use Proximum\Vimeet\Infrastructure\Repository\AdminRepository;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private \DateTimeInterface $dateTime;
    private AdminRepository $adminRepository;
    private AuthenticationManager $authenticationManager;
    private AuthenticationUtils $authenticationUtils;
    private TranslatorInterface $translator;

    public function __construct(
        DateTimeInterface $dateTime,
        AdminRepositoryInterface $adminRepository,
        AuthenticationManager $authenticationManager,
        AuthenticationUtils $authenticationUtils,
        TranslatorInterface $translator
    ) {
        $this->dateTime = $dateTime;
        $this->adminRepository = $adminRepository;
        $this->authenticationManager = $authenticationManager;
        $this->authenticationUtils = $authenticationUtils;
        $this->translator = $translator;
    }

    public function loginAction(bool $isDebugMode, string $appEnv): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('admin_event_list');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();

        $admin = ['username' => $this->authenticationUtils->getLastUsername()];

        $form = $this->createForm(LoginType::class, $admin, [
            'action' => $this->generateUrl('admin_login_check'),
        ]);

        $now = $this->dateTime;

        $email = $this->authenticationUtils->getLastUsername();
        if ($email !== null) {
            $admin = $this->adminRepository->findByEmail($email);
        } else {
            $admin = null;
        }

        if (null !== $admin && $admin->isTemporarilyDisabledDueToFailedAuthentication($now)) {
            return $this->render(
                '@Admin/Security/account_temporarily_disabled.html.twig', [
                'username' => $email,
                'admins' => [],
            ]);
        }

        if ($error instanceof BadCredentialsException && null !== $admin) {
            $remainingAuthenticationAttempt = $admin->getRemainingAuthenticationAttempt($now);

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

        $admins = ($isDebugMode && $appEnv === 'dev') ? $this->adminRepository->all() : [];

        return $this->render('AdminBundle:Security:login.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
            'admins' => $admins,
        ]);
    }

    public function loginUserAction(Admin $admin): RedirectResponse
    {
        $this->authenticationManager->authenticate($admin, 'admin');

        return $this->redirectToRoute('admin_event_list');
    }
}
