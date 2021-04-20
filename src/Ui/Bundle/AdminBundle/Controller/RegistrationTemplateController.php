<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\AddLocale;
use Proximum\Vimeet\Application\Command\Template\Registration\Update;
use Proximum\Vimeet\Application\Components\Sheet\Template\CompletenessCalculator;
use Proximum\Vimeet\Domain\Exception\Nomenclature\NomenclatureNotFoundException;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateException;
use Proximum\Vimeet\Domain\Template\Exception\TemplateException;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\AddLocaleType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Registration\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationTemplateController extends AbstractController
{
    private RegistrationTemplateRepositoryInterface $registrationTemplateRepository;
    private EventRepositoryInterface $eventRepository;
    private CompletenessCalculator $sheetTemplateCompletenessCalculator;
    private TranslatorInterface $translator;
    private CommandBusInterface $commandBus;

    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        EventRepositoryInterface $eventRepository,
        CompletenessCalculator $sheetTemplateCompletenessCalculator,
        TranslatorInterface $translator,
        CommandBusInterface $commandBus
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->eventRepository = $eventRepository;
        $this->sheetTemplateCompletenessCalculator = $sheetTemplateCompletenessCalculator;
        $this->translator = $translator;
        $this->commandBus = $commandBus;
    }

    public function listAction(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $templates          = $this->registrationTemplateRepository->getBaseTemplates();
        $events             = $this->eventRepository->getListByAdmin($this->getUser());
        $templatesOrganizer = $this->registrationTemplateRepository->getTemplateForGivenEvents($events);

        return $this->render('AdminBundle:RegistrationTemplate:list.html.twig', [
            'templates'          => $templates,
            'templatesOrganizer' => $templatesOrganizer,
        ]);
    }

    public function updateJsonAction(
        Request $request,
        RegistrationTemplate $registrationTemplate,
        string $locale
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $registrationTemplate);

        $update     = new Update($registrationTemplate);
        $updateForm = $this->createForm(UpdateType::class, $update, [
            'submit' => true,
        ]);

        $addLocaleForm = null;

        if (!$registrationTemplate->getEvent()) {
            $addLocaleForm = $this->createForm(AddLocaleType::class, new AddLocale($registrationTemplate), [
                'action'   => $this->generateUrl('admin_template_registration_add_locale',
                    ['template' => $registrationTemplate->getId()]),
                'submit'   => true,
                'template' => $registrationTemplate,
            ]);
        }

        $completeness  = $this->sheetTemplateCompletenessCalculator->compute($registrationTemplate);
        $incompletes   = array_keys(array_filter($completeness, function ($percent) { return $percent < 100; }));

        if ($updateForm->handleRequest($request)->isSubmitted() && $updateForm->isValid()) {
            try {
                $this->commandBus->handle($update);
                $this->addFlash('success', 'flash.admin.template.registration.update.success');

                return $this->redirectToRoute('admin_template_registration_json', [
                    'registrationTemplate' => $registrationTemplate->getId(),
                    'locale' => $locale,
                ]);
            } catch (NomenclatureNotFoundException $exception) {
                $this->addFlash('error', 'flash.admin.template.registration.update.error.nomenclatureNotFound');
                $updateForm->get('value')->addError(
                    new FormError(
                        $this->translator->trans('validators.admin.template.registration.update.error.nomenclatureNotFound', [], 'validators')
                    )
                );
            } catch (RegistrationTemplateException $registrationTemplateException) {
                $this->addFlash('error', $registrationTemplateException->getMessage());
            } catch (TemplateException $exception) {
                $this->addFlash('error', 'flash.admin.template.registration.update.error.template');
                $updateForm->get('value')->addError(
                    new FormError(
                        $this->translator->trans('validators.admin.template.registration.update.error.template', [], 'validators')
                    )
                );
            }
        }

        // Add warning if some locales translations are incompletes
        if (!empty($incompletes)) {
            $this->addFlash('warning', 'flash.template.incomplete_translations.warning');
        }

        return $this->render('AdminBundle:RegistrationTemplate:updateJson.html.twig', [
            'template'        => $registrationTemplate,
            'locale'          => $locale,
            'form'            => $updateForm->createView(),
            'add_locale_form' => $addLocaleForm ? $addLocaleForm->createView() : null,
            'completeness'    => $completeness,
            'event'           => $registrationTemplate->getEvent(),
        ]);
    }

    public function addLocaleAction(Request $request, RegistrationTemplate $template): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted(AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT, $template);

        $addLocale     = new AddLocale($template);
        $addLocaleForm = $this->createForm(AddLocaleType::class, $addLocale, [
            'action'   => $this->generateUrl('admin_template_registration_add_locale', ['template' => $template->getId()]),
            'submit'   => true,
            'template' => $template,
        ]);

        if ($addLocaleForm->handleRequest($request)->isSubmitted()) {
            if ($addLocaleForm->isValid()) {
                $this->commandBus->handle($addLocale);

                return $this->redirectToRoute('admin_template_registration_build', [
                    'registrationTemplate' => $template->getId(),
                    'locale' => $addLocale->locale,
                ]);
            } else {
                $this->addFlash('error', (string) $addLocaleForm->getErrors(true));
            }
        }

        return $this->redirectToRoute('admin_template_registration_build', [
            'template' => $template->getId(),
            'locale'   => $template->getFallback(),
        ]);
    }
}
