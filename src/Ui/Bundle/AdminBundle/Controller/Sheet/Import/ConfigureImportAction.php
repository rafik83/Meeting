<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet\Import;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Participant\Import;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\ImportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ConfigureImportAction
{
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

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->twig = $twig;
    }

    public function __invoke(
        Request $request,
        Event $event,
        AdminDomain $adminDomain
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ADMIN')
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $import = new Import();
        $form = $this->formFactory->create(ImportType::class, $import, [
            'event' => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'user' => $adminDomain->getAdmin(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($import);

            return new RedirectResponse($this->router->generate('admin_sheet_import_mapping', [
                'event' => $event->getId(),
                'type'  => $import->type->getId(),
            ]));
        }

        return new Response($this->twig->render('AdminBundle:Sheet:import.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]));
    }
}
