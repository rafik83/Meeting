<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\StaticFormulation;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\StaticFormulation\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\StaticFormulation\CreateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;
use function array_key_exists;

class CreateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $engine;

    /** @var TranslatorInterface */
    private $translator;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var StaticFormulationRepositoryInterface */
    private $staticFormulationRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        EngineInterface $engine,
        TranslatorInterface $translator,
        TypeRepositoryInterface $typeRepository,
        StaticFormulationRepositoryInterface $staticFormulationRepository
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->engine = $engine;
        $this->translator = $translator;
        $this->typeRepository = $typeRepository;
        $this->staticFormulationRepository = $staticFormulationRepository;
    }

    public function __invoke(Request $request, Event $event, string $key)
    {
        if (!array_key_exists($key, Constant::STATIC_FORMULATION_LIST)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $staticFormulations = $this->staticFormulationRepository->findByEventAndKey($event, $key);
        $remainingTypes = $this->typeRepository->getTypesByEvent($event);

        // Allow only the remaining types to be attached to this new static formulation for this key
        foreach ($staticFormulations as $staticFormulation) {
            foreach ($staticFormulation->getTypes() as $type) {
                if (isset($remainingTypes[$type->getId()])) {
                    unset($remainingTypes[$type->getId()]);
                }
            }
        }

        if (empty($remainingTypes)) {
            $this->flashBag->add('error', 'flash.event.staticFormulation.type.empty.error');

            return new RedirectResponse($this->router->generate('admin_event_static_formulation_list', [
                'event' => $event->getId(),
            ]));
        }

        $titles = [];
        foreach ($event->getLocales() as $locale) {
            $titles[$locale] = $this->translator->trans(Constant::STATIC_FORMULATION_LIST[$key]['label'], [], 'messages', $locale);
        }

        $locale = $event->getAvailableLocale($request->getLocale());
        $command = new Create($event, $key, $titles);
        $form = $this->formFactory->create(CreateType::class, $command, [
            'submit' => true,
            'locale' => $locale,
            'remainingTypes' => $remainingTypes,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->flashBag->add('success', 'flash.event.staticFormulation.created.success');

            return new RedirectResponse($this->router->generate('admin_event_static_formulation_list', [
                'event' => $event->getId(),
            ]));
        }

        return new Response($this->engine->render('AdminBundle:StaticFormulation:create.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'originTitle' => $titles[$locale],
        ]));
    }
}
