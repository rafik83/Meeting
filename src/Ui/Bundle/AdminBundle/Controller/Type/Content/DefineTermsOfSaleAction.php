<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\Content;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Type\Content\DefineTermsOfSale;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface as EventContentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Type\ContentRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\Content\DefineTermsOfSaleType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class DefineTermsOfSaleAction
{
    /** @var ContentRepositoryInterface */
    private $contentRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EventContentRepositoryInterface */
    private $eventContentRepository;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EventContentRepositoryInterface $eventContentRepository,
        ContentRepositoryInterface $contentRepository,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->contentRepository = $contentRepository;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->eventContentRepository = $eventContentRepository;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Request $request, Event $event, Type $type)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $type->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $content = $this->contentRepository->findByTypeAndAssociatedParticipationType(Type\Content::TYPE_TERMS_OF_SALE, $type);
        $contentFallback = $this->eventContentRepository->findByEventAndType($event, Event\Content::TYPE_TERMS_OF_SALE);

        $define = new DefineTermsOfSale($type, $contentFallback, $content);
        $form = $this->formFactory->create(DefineTermsOfSaleType::class, $define, [
            'submit' => true,
            'event' => $event,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($define);
            $this->flashBag->add('success', 'flash.type.content.define_terms_of_sale.success');

            return new RedirectResponse($this->router->generate('admin_type_list', [
                'event' => $event->getId(),
            ]));
        }

        return new Response($this->twig->render('AdminBundle:Type/Content:define_terms_of_sale.html.twig', [
            'event' => $event,
            'type'  => $type,
            'contentDefined' => $content instanceof Type\Content,
            'typeTitle' => $type->getTitle($event->getAvailableLocale($request->getLocale())),
            'form'  => $form->createView(),
        ]));
    }
}
