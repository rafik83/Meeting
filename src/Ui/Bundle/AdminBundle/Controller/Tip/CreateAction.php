<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Create;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\CreateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreateAction
{
    const TEMPLATE = 'AdminBundle:Tip:create.html.twig';

    /** @var CommandBus */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $engine;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var array */
    private $defaultLocales;

    /**
     * @param CommandBus                           $commandBus
     * @param FormFactoryInterface                 $formFactory
     * @param FlashBagInterface                    $flashBag
     * @param RouterInterface                      $router
     * @param EngineInterface                      $engine
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param array                                $defaultLocales
     */
    public function __construct(
        CommandBus $commandBus,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        array $defaultLocales
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->defaultLocales = $defaultLocales;
    }

    /**
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return Response|RedirectResponse
     */
    public function __invoke(Request $request): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Access denied');
        }

        $command = new Create($this->defaultLocales);
        $form    = $this->formFactory->create(CreateType::class, $command, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->flashBag->add('success', 'flash.admin.tip.create.success');

            return new RedirectResponse($this->router->generate('admin_tip_list'));
        }

        return $this->engine->renderResponse(self::TEMPLATE, [
            'form' => $form->createView(),
        ]);
    }
}
