<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\CustomLink;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Event\CustomLink\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\CustomLink\CreateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class CreateAction
{
    private Environment $twig;
    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    private CommandBusInterface $commandBus;
    private FormFactoryInterface $formFactory;
    private RouterInterface $router;
    private FlashBagInterface $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $queryBus,
        Environment $twig,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        FlashBagInterface $flashBag
    ) {
        $this->twig = $twig;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $queryBus;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException();
        }

        $locale = $event->getAvailableLocale($request->getLocale());

        $command = new Create($event, $event->getLocales());
        $form = $this->formFactory->create(
            CreateType::class,
            $command,
            [
                'submit' => true,
                'locale' => $locale,
                'event' => $event,
            ]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->flashBag->add('success', 'flash.admin.custom_links.created.success');

            return new RedirectResponse(
                $this->router->generate(
                    'admin_event_custom_links_list',
                    [
                        'event' => $event->getId(),
                    ]
                )
            );
        }

        return new Response(
            $this->twig->render(
                'AdminBundle:CustomLink:create.html.twig',
                [
                    'form' => $form->createView(),
                    'event' => $event,
                ]
            )
        );
    }
}
