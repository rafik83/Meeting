<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\ExtraParameter;

use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\ExtraParameter\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateController extends Controller
{
    /**
     * @param Request        $request
     * @param Event          $event
     * @param ExtraParameter $extraParameter
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function updateAction(Request $request, Event $event, ExtraParameter $extraParameter): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if ($event !== $extraParameter->getEvent()) {
            throw $this->createNotFoundException(
                sprintf('the extra parameter %s is not on the event %s', $extraParameter->getId(), $event->getId())
            );
        }

        $update = new Update($extraParameter);
        $form = $this->createForm(UpdateType::class, $update, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);

            $this->addFlash('success', 'flash.admin.event.extraParameter.update.success');

            return $this->redirectToRoute('admin_event_extra_parameter_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Event/ExtraParameter:update.html.twig', [
            'event'          => $event,
            'extraParameter' => $extraParameter,
            'form'           => $form->createView(),
        ]);
    }
}
