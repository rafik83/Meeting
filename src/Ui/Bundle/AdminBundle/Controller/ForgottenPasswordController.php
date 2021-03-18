<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Admin\ForgottenPassword;
use Proximum\Vimeet\Application\Command\Admin\NewPassword;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\Admin\ForgottenPasswordToken;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\ForgottenPasswordType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\NewPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForgottenPasswordController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function forgottenPasswordAction(Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('admin_event_list');
        }

        $forgottenPassword = new ForgottenPassword($request->getLocale());
        $form              = $this->createForm(ForgottenPasswordType::class, $forgottenPassword, [
            'action' => $this->generateUrl('admin_forgot_password'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($forgottenPassword);
            } catch (EmailDoesNotExistException $exception) {
                $this->get('logger')->error(
                    sprintf("forgotten password : email %s not found", $forgottenPassword->email)
                );
            }

            $this->addFlash('success', 'flash.admin.reset_password_token.success');

            return $this->redirectToRoute('admin_login');
        }

        return $this->render('AdminBundle:ResetPassword:request_token.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request                $request
     * @param ForgottenPasswordToken $forgottenPasswordToken
     *
     * @return RedirectResponse|Response
     */
    public function createNewPasswordAction(Request $request, ForgottenPasswordToken $forgottenPasswordToken)
    {
        if ($forgottenPasswordToken->isExpired(new \DateTime())) {
            $this->addFlash('error', 'flash.admin.reset_password_token.expired');

            return $this->redirectToRoute('admin_forgot_password');
        }

        $newPassword = new NewPassword($forgottenPasswordToken->getAdmin());
        $form        = $this->createForm(NewPasswordType::class, $newPassword, [
            'action' => $this->generateUrl('admin_create_new_password', [
                'token' => $forgottenPasswordToken->getToken(),
            ]),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($newPassword);
            $this->get('adapter.authentication_manager')->authenticate($forgottenPasswordToken->getAdmin(), 'admin');
            $this->addFlash('success', 'flash.new_password.success');

            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('AdminBundle:ResetPassword:new_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
