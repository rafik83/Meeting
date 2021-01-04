<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Type\PackageNotRequiredException;
use Proximum\Vimeet\Application\Command\Type\Update;
use Proximum\Vimeet\Application\Exception\Type\TypeAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\TypeUpdateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        EngineInterface $engine
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->engine = $engine;
        $this->router = $router;
        $this->translator = $translator;
        $this->commandBus = $commandBus;
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Type    $type
     *
     * @return Response|RedirectResponse
     */
    public function __invoke(Request $request, Event $event, Type $type): Response
    {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $type->getEvent()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $update = new Update($type, $event->getAvailableLocale($request->getLocale()));
        $form = $this->formFactory->create(TypeUpdateType::class, $update, [
            'event' => $event,
            'type' => $type,
            'showAnalyticsSettings' => $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN'),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($update);
                $this->flashBag->add('success', 'flash.admin.type.update.success');

                return new RedirectResponse(
                    $this->router->generate('admin_type_list', ['event' => $event->getId()])
                );
            } catch (TypeAlreadyExistsException $typeAlreadyExistsException) {
                $error = new FormError($this->translator->trans('admin.type.already_exists'));

                foreach ($typeAlreadyExistsException->getLocales() as $locale) {
                    $form->get('translations')->get($locale)->get('title')->addError($error);
                }
            } catch (PackageNotRequiredException $packageNotRequiredException) {
                $errorPayment = new FormError($this->translator->trans('admin.type.no_required_package'));

                $form->get('isPaymentRequired')->addError($errorPayment);
            }
        }

        return $this->engine->renderResponse('AdminBundle:Type:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
