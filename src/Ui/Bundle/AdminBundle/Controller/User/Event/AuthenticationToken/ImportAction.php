<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event\AuthenticationToken;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\Event\AuthenticationTokenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ImportAction
{
    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        Environment $twig,
        FormFactoryInterface $formFactory,
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus,
        RouterInterface $router
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ADMIN') ||
            false === $this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $authenticationTokenImport = new AuthenticationTokenImport();
        $form = $this->formFactory->create(AuthenticationTokenType::class, $authenticationTokenImport, [
            'submit' => true,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var File $file */
            $file = $this->commandBus->handle($authenticationTokenImport);

            return new RedirectResponse(
                $this->router->generate('admin_user_event_authentication_token_confirm_import', [
                    'event' => $event->getId(),
                    'importedFile' => $file->getId(),
                ])
            );
        }

        return new Response($this->twig->render('@Admin/User/Event/AuthenticationToken/import.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]));
    }
}
