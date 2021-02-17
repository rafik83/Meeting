<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Messaging;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Messaging\Message\Create;
use Proximum\Vimeet\Application\Command\Messaging\Message\Update;
use Proximum\Vimeet\Application\Query\Messaging\Message\ListQuery;
use Proximum\Vimeet\Application\Query\Messaging\Message\PreviewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Message\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Message\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends AbstractController
{
    private CommandBusInterface $commandBus;
    private QueryBusInterface $queryBus;

    public function __construct(QueryBusInterface $queryBus, CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    /**
     * Displays a list of all emailing messages for a given event.
     */
    public function listAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        return $this->render('AdminBundle:Messaging\Message:list.html.twig', [
            'event'    => $event,
            'messages' => $this->queryBus->handle(new ListQuery($event)),
        ]);
    }

    /**
     * Handles creation of an emailing message.
     */
    public function createAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new Create($event);
        $form   = $this->createForm(CreateType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->addFlash('success', 'flash.messaging.message.create.success');

            return $this->redirectToRoute('admin_messaging_message_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Messaging\Message:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * Handles update of a given emailing message.
     */
    public function updateAction(Request $request, Event $event, Message $message): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new Update($message);
        $form   = $this->createForm(UpdateType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->addFlash('success', 'flash.messaging.message.update.success');

            return $this->redirectToRoute('admin_messaging_message_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Messaging\Message:edit.html.twig', [
            'event'   => $event,
            'message' => $message,
            'form'    => $form->createView(),
        ]);
    }

    public function previewAction(Request $request, Event $event, Message $message): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $messageView = $this->queryBus->handle(new PreviewQuery($message, $locale));

        return $this->render('AdminBundle:Messaging\Message:preview.html.twig', [
            'event' => $event,
            'mail'  => $messageView,
        ]);
    }
}
