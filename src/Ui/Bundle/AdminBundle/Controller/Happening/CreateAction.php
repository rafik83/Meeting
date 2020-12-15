<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\Create;
use Proximum\Vimeet\Application\Exception\Happening\SpeakerNotUserException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;

class CreateAction
{
    public const TEMPLATE = 'AdminBundle:Happening:create.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

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
        EngineInterface $engine,
        RouterInterface $router,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->router = $router;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Event $event, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access Denied!');
        }

        $create = new Create($event);
        $form = $this->formFactory->create(CreateType::class, $create, [
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

                $this->commandBus->handle($create);
                $this->flashBag->add('success', 'flash.admin.happening.create.success');

                return new RedirectResponse(
                    $this->router->generate('admin_happening_list', ['event' => $event->getId()])
                );
            } catch (SpeakerNotUserException $speakerNotUserException) {
                $error = new FormError(
                    $this->translator->trans('form.happening_create.speaker_not_user.error', [], 'forms')
                );
                $form->addError($error);
            }
        }

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
}
