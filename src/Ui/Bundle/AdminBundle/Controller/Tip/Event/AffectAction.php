<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event\AffectType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class AffectAction
{
    const TEMPLATE = 'AdminBundle:Tip:Event/affect.html.twig';

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var Environment */
    private $twig;

    private CommandBusInterface $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        CommandBusInterface $commandBus,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        Environment $twig,
        FlashBagInterface $flashBag,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->router = $router;
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->flashBag = $flashBag;
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
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $locale = $event->getAvailableLocale($request->getLocale());
        $affect = new Affect($event);
        $form   = $this->formFactory->create(AffectType::class, $affect, [
            'admin'  => $admin,
            'event'  => $event,
            'locale' => $locale,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($affect);
            $this->flashBag->add('success', 'flash.admin.tip.affect.success');

            return new RedirectResponse($this->router->generate('admin_tip_event_list', ['event' => $event->getId()]));
        }

        return new Response($this->twig->render(self::TEMPLATE, [
            'event' => $event,
            'form'  => $form->createView(),
        ]));
    }
}
