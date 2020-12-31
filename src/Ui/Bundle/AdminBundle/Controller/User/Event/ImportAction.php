<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\ThirdParty\LENI\User\Import\Import;
use Proximum\Vimeet\Application\ThirdParty\LENI\User\Import\ImportResult;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\Event\ImportType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ImportAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var EngineInterface */
    private $engine;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        EngineInterface $engine,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->engine = $engine;
        $this->flashBag = $flashBag;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $import = new Import($event);
        $form = $this->formFactory->create(ImportType::class, $import, ['submit' => true]);

        if ($form->handleRequest($request)->isValid() && $form->isSubmitted()) {
            /** @var ImportResult $importResult */
            $importResult = $this->commandBus->handle($import);
            $this->flashBag->add(
                'success',
                $this->translator->trans(
                    'flash.admin.leni_user.import.success',
                    [
                        '%countAddedUsers%' => $importResult->countAddedUsers(),
                        '%countUpdatedUsers%' => $importResult->countUpdatedUsers(),
                    ],
                    'flashes'
                )
            );

            return new RedirectResponse(
                $this->router->generate(
                    'admin_users_list',
                    [
                        'event' => $event->getId(),
                    ]
                )
            );
        }

        return new Response(
            $this->engine->render(
                '@Admin/User/Event/Import/import.html.twig',
                [
                    'event' => $event,
                    'form' => $form->createView(),
                ]
            )
        );
    }
}
