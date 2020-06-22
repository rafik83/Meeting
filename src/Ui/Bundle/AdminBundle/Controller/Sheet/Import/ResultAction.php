<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet\Import;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Participant\Import\CreateMapping;
use Proximum\Vimeet\Application\Command\Participant\Import\UpdateMapping;
use Proximum\Vimeet\Application\Query\Participant\Import\ImportResultViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\SheetImportMapping\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\SheetImportMapping\SaveType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ResultAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var ImportResultViewQueryHandler */
    private $importResultViewQueryHandler;

    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        ImportResultViewQueryHandler $importResultViewQueryHandler,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        EngineInterface $engine,
        RouterInterface $router,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->importResultViewQueryHandler = $importResultViewQueryHandler;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ADMIN')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_PARTICIPANT_IMPORT_ACCESS')
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $participantDenormalizerView = $this->importResultViewQueryHandler->handle();

        $createForm = null;
        $updateForm = null;

        $participantImport = $participantDenormalizerView->participantImport;
        if ($participantImport->hasDiffInMapping()) {
            $create = new CreateMapping(
                $event,
                $participantImport->getMapping()
            );

            $createForm = $this->formFactory->create(CreateType::class, $create, [
                'submit' => true,
            ]);

            if ($createForm->handleRequest($request)->isSubmitted() && $createForm->isValid()) {
                $this->commandBus->handle($create);
                $this->flashBag->add('success', 'flash.admin.event.sheet.import_mapping.create.success');

                return new RedirectResponse(
                    $this->router->generate('admin_sheet', [
                        'event' => $event->getId()
                    ])
                );
            }

            if ($participantImport->hasImportMapping()) {
                $updateMapping = new UpdateMapping(
                    $participantImport->getImportMapping(),
                    $participantImport->getMapping()
                );

                $updateForm = $this->formFactory->create(SaveType::class, $updateMapping);

                if ($updateForm->handleRequest($request)->isSubmitted() && $updateForm->isValid()) {
                    $this->commandBus->handle($updateMapping);

                    $this->flashBag->add('success', 'flash.admin.event.sheet.import_mapping.update.success');

                    return new RedirectResponse(
                        $this->router->generate('admin_sheet', [
                            'event' => $event->getId()
                        ])
                    );
                }
            }
        }

        return new Response($this->engine->render('AdminBundle:Sheet:importResult.html.twig', [
            'event' => $event,
            'view' => $participantDenormalizerView,
            'createForm' => $createForm !== null ? $createForm->createView() : null,
            'updateForm' => $updateForm !== null ? $updateForm->createView() : null,
        ]));
    }
}
