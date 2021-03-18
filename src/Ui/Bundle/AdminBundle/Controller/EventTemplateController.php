<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventTemplateController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function registrationTemplateAction(Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $templates = $this->get('repository.template.registration_template_repository')
                          ->getTemplateForGivenEvent($event);

        return $this->render('AdminBundle:EventTemplate:registrationTemplate.html.twig', [
            'templates' => $templates,
            'event'     => $event,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return Response
     */
    public function sheetTemplateAction(Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $templates = $this->get('repository.template.sheet_template_repository')
            ->getTemplateForGivenEvent($event);

        return $this->render('AdminBundle:EventTemplate:sheetTemplate.html.twig', [
            'templates'     => $templates,
            'event'         => $event,
        ]);
    }

    /**
     * @param Request     $request
     * @param Event       $event
     * @param AdminDomain $adminDomain
     *
     * @return Response|RedirectResponse
     */
    public function packageTemplateAction(Request $request, Event $event, AdminDomain $adminDomain): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $templates = $this->get('repository.package_repository')->findByEvent($event);

        $create = new Create($event);
        $form   = $this->createForm(CreateType::class, $create, [
            'selectEvent' => false,
            'submit'      => true,
            'user'        => $adminDomain->getAdmin(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_event_template_package', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:EventTemplate:packageTemplate.html.twig', [
            'form'      => $form->createView(),
            'templates' => $templates,
            'event'     => $event,
        ]);
    }
}
