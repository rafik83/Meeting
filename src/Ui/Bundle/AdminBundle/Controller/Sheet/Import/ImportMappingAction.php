<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet\Import;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Participant\ImportMapping;
use Proximum\Vimeet\Application\Query\Participant\Import\ImportMappingViewQuery;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\ImportMappingType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ImportMappingAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        RouterInterface $router,
        EngineInterface $engine,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->flashBag = $flashBag;
    }

    public function __invoke(
        Request $request,
        Event $event,
        Type $type,
        AdminDomain $adminDomain
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ADMIN')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_PARTICIPANT_IMPORT_ACCESS')
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $locale = $event->getAvailableLocale($request->getLocale());

        $importMappingViewQuery = new ImportMappingViewQuery($type, $locale);

        try {
            /** @var ImportMappingView $importMappingView */
            $importMappingView = $this->queryBus->handle($importMappingViewQuery);
        } catch (\Exception $exception) {
            $this->flashBag->add('error', 'flash.admin.sheet.participant.import.error');

            return new RedirectResponse($this->router->generate('admin_sheet_import', ['event' => $event->getId()]));
        }

        $importMapping = new ImportMapping(
            $event,
            $type,
            $adminDomain->getAdmin(),
            $locale,
            $importMappingView
        );

        $form = $this->formFactory->create(ImportMappingType::class, $importMapping, [
            'locale' => $locale,
            'importMappingView' => $importMappingView,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($importMapping);

            return new RedirectResponse($this->router->generate('admin_sheet_import_result', [
                'event' => $event->getId(),
                'type'  => $type->getId(),
            ]));
        }

        return new Response($this->engine->render('AdminBundle:Sheet:importMapping.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]));
    }
}
