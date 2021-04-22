<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventTemplateController extends AbstractController
{
    private RegistrationTemplateRepositoryInterface $registrationTemplateRepository;
    private SheetTemplateRepositoryInterface $sheetTemplateRepository;
    private PackageRepositoryInterface $packageRepository;
    private CommandBusInterface $commandBus;

    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        PackageRepositoryInterface $packageRepository,
        CommandBusInterface $commandBus
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->sheetTemplateRepository = $sheetTemplateRepository;
        $this->packageRepository = $packageRepository;
        $this->commandBus = $commandBus;
    }

    public function registrationTemplateAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $templates = $this->registrationTemplateRepository->getTemplateForGivenEvent($event);

        return $this->render('AdminBundle:EventTemplate:registrationTemplate.html.twig', [
            'templates' => $templates,
            'event' => $event,
        ]);
    }

    public function sheetTemplateAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $templates = $this->sheetTemplateRepository
            ->getTemplateForGivenEvent($event);

        return $this->render('AdminBundle:EventTemplate:sheetTemplate.html.twig', [
            'templates' => $templates,
            'event' => $event,
        ]);
    }

    public function packageTemplateAction(Request $request, Event $event, AdminDomain $adminDomain): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $templates = $this->packageRepository->findByEvent($event);

        $create = new Create($event);
        $form = $this->createForm(CreateType::class, $create, [
            'selectEvent' => false,
            'submit' => true,
            'user' => $adminDomain->getAdmin(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);

            return $this->redirectToRoute('admin_event_template_package', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:EventTemplate:packageTemplate.html.twig', [
            'form' => $form->createView(),
            'templates' => $templates,
            'event' => $event,
        ]);
    }
}
