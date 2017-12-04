<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\PaymentConditions\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\PaymentConditions\UpdateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

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

    /** @var CommandBus */
    private $commandBus;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param FormFactoryInterface                 $formFactory
     * @param EngineInterface                      $engine
     * @param RouterInterface                      $router
     * @param FlashBagInterface                    $flashBag
     * @param CommandBus                           $commandBus
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        CommandBus $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory                 = $formFactory;
        $this->engine                      = $engine;
        $this->router                      = $router;
        $this->flashBag                    = $flashBag;
        $this->commandBus                  = $commandBus;
    }

    public function __invoke(Request $request, Event $event, Type $type): Response
    {
        $update = new Update($type);
        $form = $this->formFactory->create(UpdateType::class, $update, [
            'event'  => $event,
            'submit' => true,
        ]);

        return $this->engine->renderResponse(self::TEMPLATE, [
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'event' => $event,
            'type'  => $type,
            'form'  => $form->createView(),
        ]);
    }
}
