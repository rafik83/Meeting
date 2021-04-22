<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\PromotionCode\Batch;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\PromotionCode\Batch\Update;
use Proximum\Vimeet\Application\Query\PromotionCode\Batch\CanBeUpdatable;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\Batch\UpdateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class UpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var CanBeUpdatable */
    private $canBeUpdatable;

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
        CanBeUpdatable $canBeUpdatable,
        CommandBusInterface $commandBus,
        Environment $twig,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->canBeUpdatable = $canBeUpdatable;
        $this->commandBus = $commandBus;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, Event $event, PromotionCodeGroup $promotionCodeGroup): Response
    {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $promotionCodeGroup->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        if (!$this->canBeUpdatable->isSatisfiableBy($promotionCodeGroup)) {
            throw new AccessDeniedException('This promotion code group can not be updatable');
        }

        $update = new Update($promotionCodeGroup);
        $form = $this->formFactory->create(
            UpdateType::class,
            $update,
            [
                'submit' => true,
                'event' => $event,
                'locale' => $event->getAvailableLocale($request->getLocale()),
            ]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.promotion_code.batch_update.success');

            return new RedirectResponse(
                $this->router->generate('admin_promotion_code_batch_list', ['event' => $event->getId()])
            );
        }

        return new Response(
            $this->twig->render(
                '@Admin/PromotionCode/Batch/update.html.twig',
                [
                    'event' => $event,
                    'form' => $form->createView(),
                ]
            )
        );
    }
}
