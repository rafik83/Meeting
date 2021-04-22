<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\PromotionCode\Batch;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\PromotionCode\Batch\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\Batch\CreateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class CreateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var Environment */
    private $twig;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus,
        Environment $twig,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, Event $event)
    {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $create = new Create($event);
        $form = $this->formFactory->create(
            CreateType::class,
            $create,
            [
                'submit' => true,
                'event' => $event,
                'locale' => $event->getAvailableLocale($request->getLocale()),
            ]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);

            $this->flashBag->add('success', 'flash.promotion_code.batch_create.success');

            return new RedirectResponse(
                $this->router->generate('admin_promotion_code_batch_list', ['event' => $event->getId()])
            );
        }

        return new Response(
            $this->twig->render(
                '@Admin/PromotionCode/Batch/create.html.twig',
                [
                    'event' => $event,
                    'form' => $form->createView(),
                ]
            )
        );
    }
}
