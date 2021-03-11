<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\LinkedSheets;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\AlreadyLinkedException;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\Create;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\HasScheduledMeetingException;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\LinkedSheetsTypeUniquenessException;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\NotEnoughSheetsException;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Sheet\SheetsForNewLinkedSheetsQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\SheetView;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\LinkedSheets\CreateType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AddAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var CommandBusInterface */
    private $commandBus;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus,
        Environment $twig,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $sheetsForNewLinkedSheetsQuery = new SheetsForNewLinkedSheetsQuery($event);
        /** @var SheetView[] $sheetsNewLinkedSheetsView */
        $sheetsNewLinkedSheetsView = $this->queryBus->handle($sheetsForNewLinkedSheetsQuery);

        $command = new Create($event);
        $form = $this->formFactory->create(
            CreateType::class,
            $command,
            ['sheetViews' => $sheetsNewLinkedSheetsView]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($command);
                $this->flashBag->add('success', 'flash.admin.linked_sheets.create.success');

                return new RedirectResponse(
                    $this->router->generate('admin_linked_sheets_list', ['event' => $event->getId()])
                );
            } catch (SheetNotFoundException $exception) {
                $form->addError(
                    new FormError(
                        $this->translator->trans('validators.linkedSheets.add.sheetNotFound', [], 'validators')
                    )
                );
            } catch (AlreadyLinkedException $exception) {
                $form->addError(
                    new FormError(
                        $this->translator->trans('validators.linkedSheets.add.sheetAlreadyLinked', [], 'validators')
                    )
                );
            } catch (LinkedSheetsTypeUniquenessException $exception) {
                $form->addError(
                    new FormError(
                        $this->translator->trans('validators.linkedSheets.add.notUniqueType', [], 'validators')
                    )
                );
            } catch (NotEnoughSheetsException $exception) {
                $form->addError(
                    new FormError(
                        $this->translator->trans('validators.linkedSheets.add.notEnoughSheets', [], 'validators')
                    )
                );
            } catch (HasScheduledMeetingException $exception) {
                $form->addError(
                    new FormError(
                        $this->translator->trans('validators.linkedSheets.add.hasScheduledMeeting', [], 'validators')
                    )
                );
            }
        }

        return new Response($this->twig->render(
            '@Admin/LinkedSheets/create.html.twig',
            [
                'event' => $event,
                'form'  => $form->createView(),
            ]
        ));
    }
}
