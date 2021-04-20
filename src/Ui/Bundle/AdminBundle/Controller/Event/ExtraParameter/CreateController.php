<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\ExtraParameter;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Create;
use Proximum\Vimeet\Domain\Exception\Event\ExtraParameter\ExtraParameterAlreadyExistForThisTypeAndEventException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\ExtraParameter\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateController extends AbstractController
{
    private TranslatorInterface $translator;
    private CommandBusInterface $commandBus;

    public function __construct(
        TranslatorInterface $translator,
        CommandBusInterface $commandBus
    ) {
        $this->translator = $translator;
        $this->commandBus = $commandBus;
    }

    public function createAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $create = new Create($event);
        $form = $this->createForm(CreateType::class, $create, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($create);

                $this->addFlash('success', 'flash.admin.event.extraParameter.create.success');

                return $this->redirectToRoute('admin_event_extra_parameter_list', [
                    'event' => $event->getId(),
                ]);
            } catch (ExtraParameterAlreadyExistForThisTypeAndEventException $exception) {
                $form->get('type')->addError(
                    new FormError(
                        $this->translator->trans(
                            'validators.event.extraParameter.alreadyExistForThisTypeAndEvent',
                            [],
                            'validators'
                        )
                    )
                );
            }
        }

        return $this->render('AdminBundle:Event/ExtraParameter:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
