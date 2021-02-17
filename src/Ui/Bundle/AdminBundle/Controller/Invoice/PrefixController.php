<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Invoice;

use Proximum\Vimeet\Application\Command\InvoicePrefix\Create;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\InvoicePrefix\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrefixController extends Controller
{
    /**
     * @return Response
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        return $this->render('AdminBundle:Invoice:list.html.twig', [
            'list' => $this->get('repository.invoice.prefix_repository')->getAll(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $create = new Create();

        $form = $this->createForm(CreateType::class, $create);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.invoice.create.success');

            return $this->redirectToRoute('admin_invoice_globals_list');
        }

        return $this->render('AdminBundle:Invoice:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
