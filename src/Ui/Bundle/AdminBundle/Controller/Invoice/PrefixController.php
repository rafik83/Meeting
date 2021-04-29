<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Invoice;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\InvoicePrefix\Create;
use Proximum\Vimeet\Domain\Repository\Invoice\PrefixRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\InvoicePrefix\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrefixController extends AbstractController
{
    private PrefixRepositoryInterface $invoicePrefixRepository;
    private CommandBusInterface $commandBus;

    public function __construct(
        PrefixRepositoryInterface $invoicePrefixRepository,
        CommandBusInterface $commandBus
    ) {
        $this->invoicePrefixRepository = $invoicePrefixRepository;
        $this->commandBus = $commandBus;
    }

    public function listAction(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        return $this->render('AdminBundle:Invoice:list.html.twig', [
            'list' => $this->invoicePrefixRepository->getAll(),
        ]);
    }

    public function createAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $create = new Create();

        $form = $this->createForm(CreateType::class, $create);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->addFlash('success', 'flash.admin.invoice.create.success');

            return $this->redirectToRoute('admin_invoice_globals_list');
        }

        return $this->render('AdminBundle:Invoice:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
