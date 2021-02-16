<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event\AuthenticationToken;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\User\Event\ConfirmAuthenticationTokenImport;
use Proximum\Vimeet\Application\Query\User\Event\AuthenticationTokenImportPreviewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\Event\AuthenticationTokenConfirmType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConfirmImportAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        QueryBusInterface $queryBus
    ) {
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, Event $event, File $importedFile): Response
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ADMIN') ||
            false === $this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        try {
            $authenticationTokenImports = $this->queryBus->handle(new AuthenticationTokenImportPreviewQuery($event, $importedFile));
        } catch (\Exception $exception) {
            return new RedirectResponse($this->router->generate('admin_sheet', ['event' => $event->getId()]));
        }

        $confirmAuthenticationToken = new ConfirmAuthenticationTokenImport($authenticationTokenImports);
        $form = $this->formFactory->create(AuthenticationTokenConfirmType::class, $confirmAuthenticationToken, [
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($confirmAuthenticationToken);

            return new RedirectResponse($this->router->generate('admin_sheet', ['event' => $event->getId()]));
        }

        return $this->engine->renderResponse('@Admin/User/Event/AuthenticationToken/importPreview.html.twig', [
            'authenticationTokenImports' => $authenticationTokenImports,
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
