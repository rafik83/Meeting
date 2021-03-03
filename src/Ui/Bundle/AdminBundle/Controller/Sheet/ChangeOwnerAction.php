<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\ChangeOwner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\ChangeOwnerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ChangeOwnerAction
{
    public const TEMPLATE = 'AdminBundle:Sheet:changeOwner.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var Environment */
    private $twig;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus,
        Environment $twig,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->twig = $twig;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function __invoke(
        Request $request,
        Event $event,
        Sheet $sheet,
        AdminDomain $adminDomain
    ): Response {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationChecker->isGranted('PERMISSION_SHEET_ACCESS', $sheet)
            || !$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        if ($sheet->getEvent() !== $event) {
            throw new NotFoundHttpException(
                sprintf(
                    'The sheet %s is not on this event %s',
                    $sheet->getId(),
                    $event->getId()
                )
            );
        }

        $locale = $event->getAvailableLocale($request->getLocale());
        $command = new ChangeOwner(
            $sheet,
            $adminDomain->getAdmin(),
            $locale
        );
        $form = $this->formFactory->create(ChangeOwnerType::class, $command, [
            'locale' => $locale,
            'sheet' => $sheet,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);

            $this->flashBag->add('success', 'flash.sheet.change_owner.success');

            return new RedirectResponse($this->router->generate(
                'admin_sheet_details',
                [
                    'event' => $event->getId(),
                    'sheet' => $sheet->getId(),
                ])
            );
        }

        return new Response($this->twig->render(self::TEMPLATE, [
            'form' => $form->createView(),
            'event' => $event,
            'sheet' => $sheet,
        ]));
    }
}
