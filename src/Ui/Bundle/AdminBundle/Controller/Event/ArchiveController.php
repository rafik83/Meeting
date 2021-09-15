<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Event\ArchiveUnArchive;
use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Exception\Event\EventAlreadyArchivedException;
use Proximum\Vimeet\Domain\Exception\Event\EventNotArchivedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\ArchiveUnArchiveType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArchiveController extends AbstractController
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

    public function archiveAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $archiveUnArchive = new ArchiveUnArchive($event);
        $form = $this->createForm(ArchiveUnArchiveType::class, $archiveUnArchive, [
            'event' => $event,
            'confirm' => 'form.archive_un_archive.confirm.submit.label',
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            if ($form->has('archive')) {
                $archiveUnArchive->archive = $form->get('archive')->isClicked();
            }
            if ($form->has('unArchive')) {
                $archiveUnArchive->unArchive = $form->get('unArchive')->isClicked();
            }

            try {
                $result = $this->commandBus->handle($archiveUnArchive);

                if (null !== $result) {
                    if (ArchiveUnArchive::ARCHIVED === $result) {
                        $translatedMessage = $this
                            ->translator
                            ->trans('flash.admin.event.archive.success', ['%domain%' => $event->getDomain()], 'flashes')
                        ;

                        $this->addFlash('success', $translatedMessage);
                    } elseif (ArchiveUnArchive::UN_ARCHIVED === $result) {
                        $this->addFlash('success', 'flash.admin.event.un_archive.success');
                    }

                    return $this->redirectToRoute('admin_event_archive', [
                        'event' => $event->getId(),
                    ]);
                }
            } catch (DayNotDefinedException $exception) {
                $form->addError(new FormError(
                   $this->translator->trans('validators.event.archive.dayNotDefined', [], 'validators')
                ));
            } catch (EventAlreadyArchivedException $exception) {
                $form->addError(new FormError(
                    $this->translator->trans('validators.event.archive.alreadyArchived', [], 'validators')
                ));
            } catch (EventNotArchivedException $exception) {
                $form->addError(new FormError(
                    $this->translator->trans('validators.event.unArchive.notArchived', [], 'validators')
                ));
            }
        }

        return $this->render('AdminBundle:Event:archive.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
