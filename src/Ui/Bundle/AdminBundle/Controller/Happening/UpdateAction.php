<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Happening\Update;
use Proximum\Vimeet\Application\Exception\Happening\SpeakerNotUserException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class UpdateAction
{
    public const TEMPLATE = 'AdminBundle:Happening:update.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var Environment */
    private $twig;

    /** @var RouterInterface */
    private $router;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        Environment $twig,
        RouterInterface $router,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->router = $router;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function __invoke(
        Request $request,
        Event $event,
        Happening $happening,
        AdminDomain $adminDomain
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $happening->getEvent()
        ) {
            throw new AccessDeniedException('Access Denied!');
        }

        $update = new Update($happening);
        $form = $this->formFactory->create(UpdateType::class, $update, [
            'admin' => $adminDomain->getAdmin(),
            'event' => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                if ($form->get('allowHls')->getData()
                    && ($form->get('end')->getData()->getTimestamp() - $form->get('begin')->getData()->getTimestamp()) > 36000) {
                    $this->flashBag->add('error', 'flash.admin.happening.error.maxDuration');
                }

                $this->commandBus->handle($update);
                $this->flashBag->add('success', 'flash.admin.happening.update.success');

                return new RedirectResponse(
                    $this->router->generate(
                        'admin_happening_update',
                        [
                            'event' => $event->getId(),
                            'happening' => $happening->getId(),
                        ]
                    )
                );
            } catch (SpeakerNotUserException $speakerNotUserException) {
                $error = new FormError(
                    $this->translator->trans('form.happening_update.speaker_not_user.error', [], 'forms')
                );
                $form->addError($error);
            }
        }

        return new Response($this->twig->render(self::TEMPLATE, [
            'event' => $event,
            'form' => $form->createView(),
            'products' => $happening->getProducts(),
            'happening' => $happening,
        ]));
    }
}
