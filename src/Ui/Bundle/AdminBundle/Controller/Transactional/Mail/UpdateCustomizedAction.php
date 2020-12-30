<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Transactional\Mail;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Transactional\Mail\UpdateCustomized;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transactional\Mail\UpdateCustomizedType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateCustomizedAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        MessageRepositoryInterface $messageRepository,
        TypeRepositoryInterface $typeRepository,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->messageRepository = $messageRepository;
        $this->typeRepository = $typeRepository;
    }

    public function __invoke(
        Request $request,
        Event $event,
        string $transactionalMailType,
        Message $message
    ): Response {
        if (!array_key_exists($transactionalMailType, Constant::TRANSACTIONAL_MAIL_LIST)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $message->getType() !== $transactionalMailType
            || $message->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $data = Constant::TRANSACTIONAL_MAIL_LIST[$transactionalMailType];
        $remainingTypes = [];

        if ($data['isCustomizableByType']) {
            $messages = $this->messageRepository->findByEventAndType($event, $transactionalMailType);

            $remainingTypes = $this->typeRepository->getTypesByEvent($event);

            foreach ($messages as $oneMessage) {
                if ($oneMessage === $message) {
                    continue;
                }

                foreach ($oneMessage->getAssociatedParticipationTypes() as $type) {
                    if (isset($remainingTypes[$type->getId()])) {
                        unset($remainingTypes[$type->getId()]);
                    }
                }
            }
        }

        $customize = new UpdateCustomized($message, $data);
        $form = $this->formFactory->create(UpdateCustomizedType::class, $customize, [
            'isCustomizableByType' => $data['isCustomizableByType'],
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'remainingTypes' => $remainingTypes,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($customize);

            $this->flashBag->add('success', 'flash.admin.transactional.mail.customize.success');

            return new RedirectResponse($this->router->generate('admin_event_transactional_mail_list', [
                'event' => $event->getId(),
            ]));
        }

        return $this->engine->renderResponse('AdminBundle:Transactional/Mail:updateCustomized.html.twig', [
            'form' => $form->createView(),
            'transactionalMailType' => $transactionalMailType,
            'event' => $event,
            'message' => $message,
            'availableParameters' => array_merge(
                Constant::TRANSACTIONAL_MAIL_GENERIC_PARAMETERS,
                $data['isCustomizableByType'] ? Constant::TRANSACTIONAL_MAIL_GENERIC_CUSTOMIZABLE_BY_TYPE_PARAMETERS : [],
                $data['availableParameters']
            )
        ]);
    }
}
