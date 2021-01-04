<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Template\Form\UpdateParameters;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Form\UpdateParametersType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class UpdateParametersAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var EngineInterface */
    private $engine;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus,
        EngineInterface $engine,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
        $this->engine = $engine;
        $this->flashBag = $flashBag;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event, FormTemplate $template): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $template->getEvent()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $updateParameters = new UpdateParameters($template);
        $form = $this->formFactory->create(UpdateParametersType::class, $updateParameters, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($updateParameters);

            $this->flashBag->add('success', 'flash.admin.form.template.update_parameters.success');

            return new RedirectResponse($this->router->generate('admin_template_form_list', [
                'event' => $event->getId()
            ]));
        }

        return new Response($this->engine->render('AdminBundle:Template/Form:updateParameters.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]));
    }
}
