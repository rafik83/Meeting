<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\User\ChangePassword;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\ChangePasswordType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\EventVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChangePasswordController extends AbstractController
{
    private CommandBusInterface $commandBus;

    public function __construct(
        CommandBusInterface $commandBus
    ) {
        $this->commandBus = $commandBus;
    }

    public function changePasswordAction(Request $request, EventDomain $eventDomain, Sheet $sheet): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(EventVoter::CHANGE_PASSWORD, $eventDomain->getEvent());

        $changePassword = new ChangePassword($this->getUser());

        $form = $this->createForm(ChangePasswordType::class, $changePassword, [
            'action' => $this->generateUrl('event_change_password', ['sheet' => $sheet->getId()]),
            'method' => 'POST',
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($changePassword);
            $this->addFlash('success', 'flash.change_password.success');

            return $this->redirectToRoute('event');
        }

        return $this->render('EventBundle:ChangePassword:change_password.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'form'  => $form->createView(),
        ]);
    }
}
