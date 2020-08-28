<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Preview;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\Template\UpdatePreview;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Template\Exception\TemplateException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template\PreviewType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class UpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        TemplateDataFactory $templateDataFactory,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->templateDataFactory = $templateDataFactory;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->engine = $engine;
    }

    public function __invoke(Request $request, SheetTemplate $template): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $template
            )
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $templateData = $this->templateDataFactory->createFromTemplate($template);
        $templateObjects = $this->templateDataFactory->getPreviewAvailableData($templateData);

        if (null === $template->getEvent()) {
            $locale = $template->getAvailableLocale($request->getLocale());
        } else {
            $locale = $template->getEvent()->getAvailableLocale($request->getLocale());
        }

        $command = new UpdatePreview($template, $templateObjects);

        $form = $this->formFactory->create(PreviewType::class, $command, [
            'templateData' => $templateData,
            'templateObjects' => $templateObjects,
            'locale' => $locale,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($command);
                $this->flashBag->add('success', 'flash.template.preview.update.success');

                return new RedirectResponse($this->router->generate('admin_template_sheet_preview_update', [
                    'template' => $template->getId(),
                ]));
            } catch (TemplateException $exception) {
                $this->flashBag->add('error', $exception->getMessage());
            }
        }

        return new Response($this->engine->render('AdminBundle:SheetTemplate:preview.html.twig', [
            'form' => $form->createView(),
            'event' => $template->getEvent(),
            'locale' => $locale,
        ]));
    }
}
