<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event\CreateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class CreateAction
{
    const TEMPLATE = 'AdminBundle:Tip:Event/create.html.twig';

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var Environment */
    private $twig;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var RouterInterface */
    private $router;

    /** @var CommandBus */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        CommandBus $commandBus,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        Environment $twig,
        FlashBagInterface $flashBag,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request       $request
     * @param Event         $event
     * @param UserInterface $admin
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function __invoke(Request $request, Event $event, UserInterface $admin): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $create = new Create($event);
        $form = $this->formFactory->create(CreateType::class, $create, [
            'admin'  => $admin,
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->flashBag->add('success', 'flash.admin.tip.event.create.success');

            return new RedirectResponse($this->router->generate('admin_tip_event_list', [
                'event' => $event->getId(),
            ]));
        }

        return new Response($this->twig->render(self::TEMPLATE, [
            'event' => $event,
            'form'  => $form->createView(),
        ]));
    }
}
