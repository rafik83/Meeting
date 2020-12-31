<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\Category;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\Category\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Category\CategoryUpdateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateAction
{
    const TEMPLATE = 'AdminBundle:Happening/Category:update.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

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

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationChecker
     * @param FormFactoryInterface                 $formFactory
     * @param CommandBusInterface                  $commandBus
     * @param FlashBagInterface                    $flashBag
     * @param RouterInterface                      $router
     * @param EngineInterface                      $engine
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        EngineInterface $engine
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
    }

    /**
     * @param Request  $request
     * @param Event    $event
     * @param Category $category
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|Response
     */
    public function __invoke(Request $request, Event $event, Category $category): Response
    {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $category->getEvent()
        ) {
            throw new AccessDeniedException();
        }

        $update = new Update($category);
        $form   = $this->formFactory->create(CategoryUpdateType::class, $update, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.admin.happening.category.update.success');

            return new RedirectResponse(
                $this->router->generate('admin_happening_category_list', ['event' => $event->getId()])
            );
        }

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
