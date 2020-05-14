<?php


namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Visio\UpdateVisioSettings;
use Proximum\Vimeet\Application\Components\Visio\VisioSettingsRetriever;
use Proximum\Vimeet\Application\Query\Visio\UpdateVisioSettingsViewQuery;
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

    /** @var VisioSettingsRetriever */
    private $visioSettingsRetriever;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        RouterInterface $router,
        VisioSettingsRetriever $visioSettingsRetriever
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->engine = $engine;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->visioSettingsRetriever = $visioSettingsRetriever;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        Event $event
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $visioSettings = $this->visioSettingsRetriever->get($event);

        $updateVisioSettings = new UpdateVisioSettings($event, $visioSettings);
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

        $updateVisioSettingsView = $this->queryBus->handle(new UpdateVisioSettingsViewQuery($event, $visioSettings));

        return new Response(
            $this->engine->render(self::TEMPLATE, [
                'event' => $event,
                'view' => $updateVisioSettingsView,
                'form' => $form->createView(),
            ])
        );
    }
}
