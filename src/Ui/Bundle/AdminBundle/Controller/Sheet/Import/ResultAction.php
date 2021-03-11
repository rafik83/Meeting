<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet\Import;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Participant\Import\CreateMapping;
use Proximum\Vimeet\Application\Command\Participant\Import\UpdateMapping;
use Proximum\Vimeet\Application\Query\Participant\Import\ImportResultViewQueryHandler;
use Proximum\Vimeet\Domain\Exception\Sheet\ImportMapping\TitleNotUniqueException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\SheetImportMapping\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\SheetImportMapping\SaveType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Symfony\Component\Translation\TranslatorInterface;

class ResultAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var ImportResultViewQueryHandler */
    private $importResultViewQueryHandler;

    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        ImportResultViewQueryHandler $importResultViewQueryHandler,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        Environment $twig,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->importResultViewQueryHandler = $importResultViewQueryHandler;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $participantDenormalizerView = $this->importResultViewQueryHandler->handle();

        $createForm = null;
        $updateForm = null;
        $existingImportMapping = false;

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
                try {
                    $this->commandBus->handle($create);
                    $this->flashBag->add('success', 'flash.admin.event.sheet.import_mapping.create.success');

                    return new RedirectResponse(
                        $this->router->generate('admin_sheet', [
                            'event' => $event->getId()
                        ])
                    );
                } catch (TitleNotUniqueException $exception) {
                    $createForm->get('title')->addError(
                        new FormError(
                            $this->translator->trans(
                                'validators.admin.sheet.import_mapping.title.not_unique',
                                [],
                                'validators'
                            )
                        )
                    );
                }
            }

            if ($participantImport->hasImportMapping()) {
                $existingImportMapping = true;
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

        return new Response($this->twig->render('AdminBundle:Sheet:importResult.html.twig', [
            'event' => $event,
            'view' => $participantDenormalizerView,
            'createForm' => $createForm !== null ? $createForm->createView() : null,
            'updateForm' => $updateForm !== null ? $updateForm->createView() : null,
            'existingImportMapping' => $existingImportMapping,
        ]));
    }
}
