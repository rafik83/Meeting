<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\ExtraParameter;

use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Create;
use Proximum\Vimeet\Domain\Exception\Event\ExtraParameter\ExtraParameterAlreadyExistForThisTypeAndEventException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\ExtraParameter\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response|RedirectResponse
     */
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
                $this->get('tactician.commandbus')->handle($create);

                $this->addFlash('success', 'flash.admin.event.extraParameter.create.success');

                return $this->redirectToRoute('admin_event_extra_parameter_list', [
                    'event' => $event->getId(),
                ]);
            } catch (ExtraParameterAlreadyExistForThisTypeAndEventException $exception) {
                $form->get('type')->addError(
                    new FormError(
                        $this->get('translator')->trans(
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
