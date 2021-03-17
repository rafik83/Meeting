<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Event\Content\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Content\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     * @param string  $type
     *
     * @return Response
     */
    public function updateAction(Request $request, Event $event, $type)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $content = $this->get('repository.event.content_repository')->findByEventAndType($event, $type);

        if (null === $content) {
            throw $this->createNotFoundException('Content not found.');
        }

        $update = new Update($content);
        $form   = $this->createForm(UpdateType::class, $update, ['submit' => true, 'content' => $content]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.content.update_' . $type . '.success');

            return $this->redirectToRoute('admin_content_update', ['event' => $event->getId(), 'type' => $type]);
        }

        return $this->render('AdminBundle:Content:update.html.twig', [
            'event' => $event,
            'type'  => $type,
            'form'  => $form->createView(),
        ]);
    }
}
