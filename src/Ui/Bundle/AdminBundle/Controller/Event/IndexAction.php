<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Event\PrepareIndex;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\IndexType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class IndexAction
{
    private const TEMPLATE = 'AdminBundle:Event:index.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var Environment */
    private $twig;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->twig = $twig;
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
            throw new AccessDeniedException('Only the super admin can access this page');
        }

        $command = new PrepareIndex();
        $form = $this->formFactory->create(IndexType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->flashBag->add('success', 'flash.admin.event.index.success');

            return new RedirectResponse($this->router->generate('admin_event_list'));
        }

        return new Response($this->twig->render(self::TEMPLATE, [
            'form' => $form->createView(),
        ]));
    }
}
