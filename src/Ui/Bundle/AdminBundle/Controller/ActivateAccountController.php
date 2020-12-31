<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Admin\ActivateAccountPassword;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\ActivateAccountPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateAccountController extends Controller
{
    /**
     * @param Request              $request
     * @param ActivateAccountToken $activateAccountToken
     *
     * @return RedirectResponse|Response
     */
    public function passwordAction(Request $request, ActivateAccountToken $activateAccountToken)
    {
        $admin = $activateAccountToken->getAdmin();

        if ($activateAccountToken->isExpired(new \DateTime())) {
            throw $this->createNotFoundException('The token is expired.');
        }

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        $command = new ActivateAccountPassword($admin);
        $form    = $this->createForm(ActivateAccountPasswordType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->get('adapter.authentication_manager')->authenticate($command->admin, 'admin');
            $this->addFlash('success', 'flash.admin.activate_account.success');

            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('AdminBundle:ActivateAccount:password.html.twig', [
            'form'      => $form->createView(),
        ]);
    }
}
