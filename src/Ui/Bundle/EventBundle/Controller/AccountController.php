<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\User\ChangeMail;
use Proximum\Vimeet\Application\Command\User\ChangeMailActivation;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Application\Exception\User\SameEmailException;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\ChangeMailType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\EventVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountController extends AbstractController
{
    private AuthenticationManagerInterface $authenticationManager;
    private CommandBusInterface $commandBus;

    public function __construct(
        AuthenticationManagerInterface $authenticationManager,
        CommandBusInterface $commandBus
    ) {
        $this->authenticationManager = $authenticationManager;
        $this->commandBus = $commandBus;
    }

    public function updateEmailAction(Request $request, EventDomain $eventDomain, Sheet $sheet, UserInterface $user = null): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(EventVoter::CHANGE_EMAIL, $eventDomain->getEvent());

        $changeMail = new ChangeMail($user, $eventDomain->getEvent());
        $form = $this->createForm(ChangeMailType::class, $changeMail, [
            'action' => $this->generateUrl('event_account_change_mail', ['sheet' => $sheet->getId()]),
        ]);


        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($changeMail);
                $this->addFlash('success', 'flash.change_mail.success');

                return $this->redirectToRoute('event');
            } catch (EmailAlreadyExistsException $exception) {
                $form->get('mail')->addError(new FormError('validators.emailAlreadyExist'));
            } catch (SameEmailException $exception) {
                $form->get('mail')->addError(new FormError('validators.sameEmail'));
            }
        }

        return $this->render('EventBundle:User:update_account.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'form' => $form->createView(),
        ]);
    }

    public function activateNewMailAction(EventDomain $eventDomain, ChangeMailToken $changeMailToken): RedirectResponse
    {
        if ($changeMailToken->isExpired(new \DateTime())) {
            throw $this->createNotFoundException('The token expired.');
        }

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->authenticationManager->disconnect();
        }

        $user = $changeMailToken->getUser();
        $changeMailActivation = new ChangeMailActivation($changeMailToken);

        $this->commandBus->handle($changeMailActivation);
        $this->authenticationManager->authenticate($user, 'main');
        $this->addFlash('success', 'flash.change_mail_activate.success');

        return $this->redirectToRoute('event');
    }
}
