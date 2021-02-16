<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event\UpdateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class UpdateAction
{
    const TEMPLATE = 'AdminBundle:Tip:Event/update.html.twig';

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var RouterInterface */
    private $router;

    /** @var CommandBus */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /**
     * @param CommandBus                           $commandBus
     * @param RouterInterface                      $router
     * @param FormFactoryInterface                 $formFactory
     * @param EngineInterface                      $engine
     * @param FlashBagInterface                    $flashBag
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     */
    public function __construct(
        CommandBus $commandBus,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        FlashBagInterface $flashBag,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request       $request
     * @param Event         $event
     * @param Tip           $tip
     * @param UserInterface $admin
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function __invoke(Request $request, Event $event, Tip $tip, UserInterface $admin): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $tip->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $update = new Update($tip);
        $form = $this->formFactory->create(UpdateType::class, $update, [
            'admin'  => $admin,
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.admin.tip.event.update.success');

            return new RedirectResponse($this->router->generate('admin_tip_event_list', [
                'event' => $event->getId(),
            ]));
        }

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
