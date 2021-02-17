<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Event\Content\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Content\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends AbstractController
{
    private ContentRepositoryInterface $contentRepository;
    private CommandBusInterface $commandBus;

    public function __construct(ContentRepositoryInterface $contentRepository, CommandBusInterface $commandBus)
    {
        $this->contentRepository = $contentRepository;
        $this->commandBus = $commandBus;
    }

    public function updateAction(Request $request, Event $event, string $type): Response
    {
        $content = $this->contentRepository->findByEventAndType($event, $type);

        if (null === $content) {
            throw $this->createNotFoundException('Content not found.');
        }

        $update = new Update($content);
        $form   = $this->createForm(UpdateType::class, $update, ['submit' => true, 'content' => $content]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
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
