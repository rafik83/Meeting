<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planner;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Planner\ImportJobCreator;
use Proximum\Vimeet\Application\Exception\Planner\InvalidXmlException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Planner\ImportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ImportController extends AbstractController
{
    private CommandBusInterface $commandBus;

    public function __construct(
        CommandBusInterface $commandBus
    ) {
        $this->commandBus = $commandBus;
    }

    public function importAction(Request $request, Event $event, UserInterface $admin): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if (!$admin instanceof Admin) {
            throw $this->createNotFoundException('Admin not found');
        }

        $importJobCreator = new ImportJobCreator($event, $admin, $request->getLocale());
        $form = $this->createForm(ImportType::class, $importJobCreator, [
            'submit'  => true,
            'confirm' => 'form.planner_import.confirm.submit.label',
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($importJobCreator);
                $this->addFlash('success', 'flash.admin.planner.import.pending');

                return $this->redirectToRoute('admin_planner', [
                    'event' => $event->getId(),
                ]);
            } catch (InvalidXmlException $exception) {
                $form->get('file')->addError(
                    new FormError(
                        'validators.planner.import.invalidXml'
                    )
                );
            }
        }

        return $this->render('AdminBundle:Planner/Import:form.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
