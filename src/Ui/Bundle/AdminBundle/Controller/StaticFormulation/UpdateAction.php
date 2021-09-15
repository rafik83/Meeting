<?php


namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\StaticFormulation;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\StaticFormulation\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\StaticFormulation\UpdateType;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class UpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var StaticFormulationRepositoryInterface */
    private $staticFormulationRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var Environment */
    private $twig;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        StaticFormulationRepositoryInterface $staticFormulationRepository,
        TypeRepositoryInterface $typeRepository,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->staticFormulationRepository = $staticFormulationRepository;
        $this->typeRepository = $typeRepository;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Event $event, string $key, StaticFormulation $staticFormulation)
    {
        if (!array_key_exists($key, Constant::STATIC_FORMULATION_LIST)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $key !== $staticFormulation->getKey()
            || $event !== $staticFormulation->getEvent()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $remainingTypesBeforeRemoval = $this->typeRepository->getTypesByEvent($event);
        $remainingTypes = $this->removeUsedRemainingTypes($remainingTypesBeforeRemoval, $staticFormulation, $event, $key);

        $locale = $event->getAvailableLocale($request->getLocale());
        $command = new Update($staticFormulation);
        $form = $this->formFactory->create(UpdateType::class, $command, [
            'submit' => true,
            'locale' => $locale,
            'remainingTypes' => $remainingTypes,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->flashBag->add('success', 'flash.event.staticFormulation.updated.success');

            return new RedirectResponse($this->router->generate('admin_event_static_formulation_list', [
                'event' => $event->getId(),
            ]));
        }

        return new Response($this->twig->render('AdminBundle:StaticFormulation:update.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'staticFormulationTitle' => $staticFormulation->getTitle($locale),
        ]));
    }

    /**
     * @return Type[]
     */
    private function removeUsedRemainingTypes(array $remainingTypes, StaticFormulation $staticFormulation, Event $event, string $key): array
    {
        $staticFormulations = $this->staticFormulationRepository->findByEventAndKey($event, $key);

        foreach ($staticFormulations as $customizedStaticFormulation) {
            if ($customizedStaticFormulation->getId() === $staticFormulation->getId()) {
                continue;
            }

            foreach ($customizedStaticFormulation->getTypes() as $type) {
                if (isset($remainingTypes[$type->getId()])) {
                    unset($remainingTypes[$type->getId()]);
                }
            }
        }

        return $remainingTypes;
    }
}
