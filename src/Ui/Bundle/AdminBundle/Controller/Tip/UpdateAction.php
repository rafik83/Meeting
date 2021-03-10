<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Update;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\UpdateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateAction
{
    const TEMPLATE = 'AdminBundle:Tip:update.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var EngineInterface */
    private $engine;

    /** @var CommandBus */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        EngineInterface $engine,
        CommandBus $commandBus,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param Tip     $tip
     *
     * @throws AccessDeniedException
     *
     * @return Response|RedirectResponse
     */
    public function __invoke(Request $request, Tip $tip): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')
            || $tip->hasEvent()) {
            throw new AccessDeniedException('Access denied');
        }

        $command = new Update($tip);
        $form = $this->formFactory->create(UpdateType::class, $command, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->flashBag->add('success', 'flash.admin.tip.update.success');

            return new RedirectResponse($this->router->generate('admin_tip_list'));
        }

        return $this->engine->renderResponse(self::TEMPLATE, [
            'form' => $form->createView(),
        ]);
    }
}
