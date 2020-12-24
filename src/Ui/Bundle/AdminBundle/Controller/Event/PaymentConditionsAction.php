<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Event\PaymentConditions\Update;
use Proximum\Vimeet\Application\Query\Type\TypesWithPaymentConditionsViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions\UpdateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PaymentConditionsAction
{
    const TEMPLATE = 'AdminBundle:Event/PaymentConditions:update.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var EngineInterface */
    private $engine;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param FormFactoryInterface                 $formFactory
     * @param RouterInterface                      $router
     * @param FlashBagInterface                    $flashBag
     * @param CommandBusInterface                  $commandBus
     * @param QueryBusInterface                    $queryBus
     * @param EngineInterface                      $engine
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @throws AccessDeniedException
     *
     * @return Response|RedirectResponse
     */
    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $update = new Update($event);
        $form = $this->formFactory->create(UpdateType::class, $update, [
            'event'  => $event,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.admin.event.paymentConditions.update.success');

            return new RedirectResponse($this->router->generate(
                'admin_event_payment_conditions',
                ['event' => $event->getId()]
            ));
        }

        $typesWithPaymentConditionsViewQuery = new TypesWithPaymentConditionsViewQuery(
            $event,
            $event->getAvailableLocale($request->getLocale())
        );
        $typeWithPaymentConditions = $this->queryBus->handle($typesWithPaymentConditionsViewQuery);

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event'                     => $event,
            'form'                      => $form->createView(),
            'typeWithPaymentConditions' => $typeWithPaymentConditions,
        ]);
    }
}
