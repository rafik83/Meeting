<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\PaymentConditions\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\PaymentConditions\UpdateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PaymentConditionsAction
{
    const TEMPLATE = 'AdminBundle:Type/PaymentConditions:update.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var CommandBusInterface */
    private $commandBus;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param FormFactoryInterface                 $formFactory
     * @param EngineInterface                      $engine
     * @param RouterInterface                      $router
     * @param FlashBagInterface                    $flashBag
     * @param CommandBusInterface                  $commandBus
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory                 = $formFactory;
        $this->engine                      = $engine;
        $this->router                      = $router;
        $this->flashBag                    = $flashBag;
        $this->commandBus                  = $commandBus;
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Type    $type
     *
     * @return Response|RedirectResponse
     */
    public function __invoke(Request $request, Event $event, Type $type): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $update = new Update($type);
        $form = $this->formFactory->create(UpdateType::class, $update, [
            'event'  => $event,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.admin.type.paymentConditions.updated');

            return new RedirectResponse($this->router->generate('admin_type_list', ['event' => $event->getId()]));
        }

        return $this->engine->renderResponse(self::TEMPLATE, [
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'event'  => $event,
            'type'   => $type,
            'form'   => $form->createView(),
        ]);
    }
}
