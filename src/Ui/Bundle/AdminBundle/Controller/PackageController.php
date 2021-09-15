<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Application\Command\Package\Duplicate;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\DuplicateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PackageController extends AbstractController
{
    private EventRepositoryInterface $eventRepository;
    private PackageRepositoryInterface $packageRepository;
    private CommandBusInterface $commandBus;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        PackageRepositoryInterface $packageRepository,
        CommandBusInterface $commandBus
    ) {
        $this->eventRepository = $eventRepository;
        $this->packageRepository = $packageRepository;
        $this->commandBus = $commandBus;
    }

    public function listAction(Request $request, AdminDomain $adminDomain): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $events = $this->eventRepository
            ->getEventsByAdmin($adminDomain->getAdmin());
        $packages = $this->packageRepository->findByEvents($events);

        $create = new Create();
        $form = $this->createForm(CreateType::class, $create, [
            'submit' => true,
            'user' => $adminDomain->getAdmin(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->commandBus->handle($create);

            return $this->redirectToRoute('admin_package_update', [
                'package' => $result->package->getId(),
            ]);
        }

        return $this->render('AdminBundle:Package:list.html.twig', [
            'packages' => $packages,
            'form' => $form->createView(),
        ]);
    }

    public function duplicateAction(Request $request, Package $package): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $duplicate = new Duplicate($package);
        $form = $this->createForm(DuplicateType::class, $duplicate, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($duplicate);
            $this->addFlash('success', 'flash.admin.template.package.duplicate.success');

            return $this->redirectToRoute('admin_package_list');
        }

        return $this->render('AdminBundle:Package:duplicate.html.twig', [
            'package' => $package,
            'form' => $form->createView(),
        ]);
    }
}
