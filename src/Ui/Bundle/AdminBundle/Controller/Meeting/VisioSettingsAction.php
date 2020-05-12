<?php


namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateVisioSettings;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Meeting\Visio\SettingsType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class VisioSettingsAction
{
    public const TEMPLATE = 'AdminBundle:Meeting/Visio:settings.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus,
        EngineInterface $engine,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->engine = $engine;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    public function __invoke(
        Request $request,
        Event $event
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $updateVisioSettings = new UpdateVisioSettings($event);
        $form = $this->formFactory->create(SettingsType::class, $updateVisioSettings, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($updateVisioSettings);

            $this->flashBag->add('success', 'flash.admin.meeting.visio.settings.update.success');

            return new RedirectResponse(
                $this->router->generate('admin_meeting_visio_settings', ['event' => $event->getId()])
            );
        }

        return new Response(
            $this->engine->render(self::TEMPLATE, [
                'event' => $event,
                'form' => $form->createView(),
            ])
        );
    }
}
