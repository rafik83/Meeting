<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Sheet\Template\AddLocale;
use Proximum\Vimeet\Application\Command\Sheet\Template\Create;
use Proximum\Vimeet\Application\Command\Sheet\Template\CreateForEvent;
use Proximum\Vimeet\Application\Command\Sheet\Template\Duplicate;
use Proximum\Vimeet\Application\Command\Sheet\Template\Update;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\AddLocaleType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\CreateForEventType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\DuplicateForEventType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\FilterSheetTemplateOrganizerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SheetTemplateController extends Controller
{
    /**
     * @param string $type
     * @param array  $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, array_merge($options, [
            'method'          => 'GET',
            'csrf_protection' => false,
            'required'        => false,
        ]));
    }

    /**
     * @param Request     $request
     * @param AdminDomain $adminDomain
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request, AdminDomain $adminDomain): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $baseTemplates   = $this->get('repository.template.sheet_template_repository')->getBaseTemplates();
        $events          = $this->get('vimeet_infrastructure.repository.event_repository')->getListByAdmin($adminDomain->getAdmin());
        $eventsTemplates = $this->get('repository.template.sheet_template_repository')->getTemplateForGivenEvents($events);

        $create = new Create($request->getLocale());
        $form   = $this->createForm(CreateType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_template_sheet_builder', [
                'template' => $result->template->getId(),
                'locale'   => $result->template->getFallback(),
            ]);
        }

        return $this->render('AdminBundle:SheetTemplate:list.html.twig', [
            'baseTemplates'   => $baseTemplates,
            'eventsTemplates' => $eventsTemplates,
            'form'            => $form->createView(),
        ]);
    }

    /**
     * @param Request     $request
     * @param AdminDomain $adminDomain
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|Response
     */
    public function listOrganizerTemplateAction(Request $request, AdminDomain $adminDomain): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $filters    = [];
        $filterForm = $this->createFilterForm(FilterSheetTemplateOrganizerType::class, $filters, [
            'admin' => $adminDomain->getAdmin(),
        ]);
        $filtered   = $filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid();

        if ($filtered) {
            $filters = $filterForm->getData();
        }

        $filterFormView = $filterForm->createView();
        $filterSummary  = $this->get('filter_summary')->getFilters(
            $filterFormView,
            $filters,
            null,
            $request->getLocale()
        );

        $events             = $this->get('vimeet_infrastructure.repository.event_repository')->getListByAdmin($this->getUser());
        $baseTemplates      = $this->get('repository.template.sheet_template_repository')->getBaseTemplates();
        $organizerTemplates = $this->get('repository.template.sheet_template_repository')->getOrganizerTemplates($events, $filters);

        $create = new CreateForEvent();
        $form   = $this->createForm(CreateForEventType::class, $create, [
            'submit' => true,
            'admin'  => $adminDomain->getAdmin(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_template_sheet_builder', [
                'template' => $result->template->getId(),
                'locale'   => $result->template->getFallback(),
            ]);
        }

        return $this->render('AdminBundle:SheetTemplate:organizerList.html.twig', [
            'base_templates'      => $baseTemplates,
            'organizer_templates' => $organizerTemplates,
            'form'                => $form->createView(),
            'filter_form'         => $filterFormView,
            'filters_summary'     => $filterSummary,
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     * @param AdminDomain   $adminDomain
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|Response
     */
    public function duplicateAction(Request $request, SheetTemplate $template, AdminDomain $adminDomain): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $template);

        $duplicate = new Duplicate($template, new \DateTime());
        $form      = $this->createForm(DuplicateForEventType::class, $duplicate, [
            'action' => $this->generateUrl('admin_template_sheet_duplicate', [
                'template' => $template->getId(),
            ]),
            'admin'  => $adminDomain->getAdmin(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($duplicate);

            return $this->redirectToRoute('admin_template_sheet_builder', [
                'template' => $result->template->getId(),
                'locale'   => $result->template->getFallback(),
            ]);
        }

        return $this->render('AdminBundle:SheetTemplate:duplicate.html.twig', [
            'template' => $template,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     * @param AdminDomain   $adminDomain
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|Response
     */
    public function duplicateOrganizerTemplateAction(Request $request, SheetTemplate $template, AdminDomain $adminDomain): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $template);

        $duplicate = new Duplicate($template, new \DateTime());

        $form = $this->createForm(DuplicateForEventType::class, $duplicate, [
            'action' => $this->generateUrl('admin_organizer_template_sheet_duplicate', [
                'template' => $template->getId(),
            ]),
            'submit' => true,
            'admin'  => $adminDomain->getAdmin(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($duplicate);

            return $this->redirectToRoute('admin_template_sheet_builder', [
                'template' => $result->template->getId(),
                'locale'   => $result->template->getFallback(),
            ]);
        }

        return $this->render('AdminBundle:SheetTemplate:duplicate.html.twig', [
            'template' => $template,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @param SheetTemplate $template
     * @param string        $locale
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function builderAction(SheetTemplate $template, $locale): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $template);

        if (!$template->hasLocale($locale)) {
            throw $this->createNotFoundException(sprintf('Locale "%s" does not exist on this template', $locale));
        }

        // Update form
        $updateForm = $this->createForm(UpdateType::class, new Update($template), [
            'action'   => $this->generateUrl('admin_template_sheet_update', [
                'template' => $template->getId(), 'locale' => $locale,
            ]),
            'submit'   => true,
            'template' => $template,
        ]);

        // Add locale form
        if (null === $template->getEvent() && $this->isGranted('ROLE_SUPER_ADMIN')) {
            $addLocaleForm = $this->createForm(AddLocaleType::class, new AddLocale($template), [
                'action'   => $this->generateUrl('admin_template_sheet_add_locale', ['template' => $template->getId()]),
                'submit'   => true,
                'template' => $template,
            ]);
        } else {
            $addLocaleForm = null;
        }

        // Queries
        $nomenclatureRepository = $this->get('repository.nomenclature_repository');
        $productRepository      = $this->get('vimeet_infrastructure.repository.product_repository');
        $completeness           = $this->get('sheet.template.completeness_calculator')->compute($template);

        $incomplete = array_keys(array_filter($completeness, function ($percent) {
            return $percent < 100;
        }));

        $nomenclatures = $template->getEvent() ?
            $nomenclatureRepository->findByEvent($template->getEvent()) :
            $nomenclatureRepository->findGlobals();

        $products = $template->getEvent() ?
            $productRepository->findOptionsByEvent($template->getEvent()) :
            null;

        // Add warning if some locales translations are incomplete
        if (!empty($incomplete)) {
            $this->addFlash('warning', 'flash.template.incomplete_translations.warning');
        }

        return $this->render('AdminBundle:SheetTemplate:builder.html.twig', [
            'add_locale_form' => $addLocaleForm ? $addLocaleForm->createView() : null,
            'completeness' => $completeness,
            'event' => $template->getEvent(),
            'locale' => $locale,
            'nomenclatures' => $nomenclatures,
            'products' => $products,
            'sheet_tags' => Tag::getSheetAndGenericTags(),
            'sheet_template_tags' => Tag::getGenericSheetTemplateTags(),
            'template'  => $template,
            'update_form' => $updateForm->createView(),
            'uploadFormats' => UploadObject::ALLOWED_FORMATS
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function addLocaleAction(Request $request, SheetTemplate $template): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $template);

        $addLocale     = new AddLocale($template);
        $addLocaleForm = $this->createForm(AddLocaleType::class, $addLocale, [
            'action'   => $this->generateUrl('admin_template_sheet_add_locale', ['template' => $template->getId()]),
            'submit'   => true,
            'template' => $template,
        ]);

        if ($addLocaleForm->handleRequest($request)->isSubmitted()) {
            if ($addLocaleForm->isValid()) {
                $this->get('tactician.commandbus')->handle($addLocale);

                return $this->redirectToRoute('admin_template_sheet_builder', [
                    'template' => $template->getId(),
                    'locale'   => $addLocale->locale,
                ]);
            } else {
                $this->addFlash('error', (string) $addLocaleForm->getErrors(true));
            }
        }

        return $this->redirectToRoute('admin_template_sheet_builder', [
            'template' => $template->getId(),
            'locale'   => $template->getFallback(),
        ]);
    }

    /**
     * @param Request       $request
     * @param SheetTemplate $template
     * @param string        $locale
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function updateAction(Request $request, SheetTemplate $template, $locale): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $template);

        $command = new Update($template);
        $form    = $this->createForm(UpdateType::class, $command, [
            'action'   => $this->generateUrl('admin_template_sheet_update', [
                'template' => $template->getId(), 'locale' => $locale,
            ]),
            'submit'   => true,
            'template' => $template,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
        }

        return $this->redirectToRoute('admin_template_sheet_builder', [
            'template' => $template->getId(),
            'locale'   => $locale,
        ]);
    }
}
