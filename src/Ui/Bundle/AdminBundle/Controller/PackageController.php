<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Application\Command\Package\Duplicate;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\DuplicateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PackageController extends Controller
{
    /**
     * @param Request     $request
     * @param AdminDomain $adminDomain
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request, AdminDomain $adminDomain)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $events = $this
            ->get('vimeet_infrastructure.repository.event_repository')
            ->getEventsByAdmin($adminDomain->getAdmin())
        ;
        $packages = $this->get('repository.package_repository')->findByEvents($events);

        $create = new Create();
        $form   = $this->createForm(CreateType::class, $create, [
            'submit' => true,
            'user'   => $adminDomain->getAdmin(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_package_update', [
                'package' => $result->package->getId(),
            ]);
        }

        return $this->render('AdminBundle:Package:list.html.twig', [
            'packages' => $packages,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Package $package
     *
     * @return Response
     */
    public function duplicateAction(Request $request, Package $package)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $duplicate = new Duplicate($package);
        $form      = $this->createForm(DuplicateType::class, $duplicate, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($duplicate);
            $this->addFlash('success', 'flash.admin.template.package.duplicate.success');

            return $this->redirectToRoute('admin_package_list');
        }

        return $this->render('AdminBundle:Package:duplicate.html.twig', [
            'package' => $package,
            'form'    => $form->createView(),
        ]);
    }
}
