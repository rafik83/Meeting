<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Order;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Command\Order\Export\ExportJobCreator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends AbstractController
{
    private FileSystemAdapterInterface $filesystem;
    private CommandBusInterface $commandBus;

    public function __construct(
        FileSystemAdapterInterface $filesystem,
        CommandBusInterface $commandBus
    ) {
        $this->filesystem = $filesystem;
        $this->commandBus = $commandBus;
    }

    public function exportAction(Request $request, UserInterface $admin, Event $event): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if (!$admin instanceof Admin) {
            throw $this->createNotFoundException('Admin not found');
        }

        $exportJobCreator = new ExportJobCreator($event, $admin, $request->getLocale());

        $this->commandBus->handle($exportJobCreator);

        $this->addFlash('success', 'flash.admin.order.export.success');

        return $this->redirectToRoute('admin_sheet_order_list', ['event' => $event->getId()]);
    }

    public function exportFileAction(Event $event, string $hash, File $file): CsvFileResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($file->getHash() !== $hash) {
            throw $this->createNotFoundException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        $path = sprintf('%s%s', $this->getParameter('infrastructure.export_order_path'), $file->getPath());

        if (!$this->filesystem->exists($path)) {
            throw $this->createNotFoundException(sprintf('File %s not found', $file->getId()));
        }

        return new CsvFileResponse(
            file_get_contents($path),
            sprintf('export_event_orders_%s.csv', date('Y_m_d_His'))
        );
    }
}
