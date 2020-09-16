<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Type\Create;
use Proximum\Vimeet\Application\Command\Type\PackageNotRequiredException;
use Proximum\Vimeet\Application\Exception\Type\TypeAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\TypeCreateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        EngineInterface $engine
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->engine = $engine;
        $this->router = $router;
        $this->translator = $translator;
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
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied for this event');
        }

        $create = new Create($event, $event->getAvailableLocale($request->getLocale()));
        $form = $this->formFactory->create(TypeCreateType::class, $create, [
            'event' => $event,
            'showAnalyticsSettings' => $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN'),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($create);
                $this->flashBag->add('success', 'flash.admin.type.create.success');

                return new RedirectResponse($this->router->generate('admin_type_list', ['event' => $event->getId()]));
            } catch (TypeAlreadyExistsException $typeAlreadyExistsException) {
                $error = new FormError($this->translator->trans('admin.type.already_exists'));

                foreach ($typeAlreadyExistsException->getLocales() as $locale) {
                    $form->get('translations')->get($locale)->get('title')->addError($error);
                }
            } catch (PackageNotRequiredException $packageNotRequiredException) {
                $errorPayment = new FormError($this->translator->trans('admin.type.no_required_package'));

                $form->get('isPaymentRequired')->addError($errorPayment);
            }
        }

        return $this->engine->renderResponse('AdminBundle:Type:create.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
}
