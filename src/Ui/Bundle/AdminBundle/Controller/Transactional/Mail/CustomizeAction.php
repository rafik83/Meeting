<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Transactional\Mail;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Transactional\Mail\Customize;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transactional\Mail\CustomizeType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use function array_key_exists;

class CustomizeAction
{
    /** @var Environment */
    private $twig;

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

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        Environment $twig,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        TranslatorInterface $translator,
        MessageRepositoryInterface $messageRepository,
        TypeRepositoryInterface $typeRepository,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $this->twig = $twig;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->messageRepository = $messageRepository;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->typeRepository = $typeRepository;
        $this->translator = $translator;
    }

    public function __invoke(Request $request, Event $event, string $transactionalMailType): Response
    {
        if (!array_key_exists($transactionalMailType, Constant::TRANSACTIONAL_MAIL_LIST)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $data = Constant::TRANSACTIONAL_MAIL_LIST[$transactionalMailType];

        $remainingTypes = [];

        if ($data['isCustomizableByType']) {
            $messages = $this->messageRepository->findByEventAndType($event, $transactionalMailType);

            $remainingTypes = $this->typeRepository->getTypesByEvent($event);

            foreach ($messages as $message) {
                foreach ($message->getAssociatedParticipationTypes() as $type) {
                    if (isset($remainingTypes[$type->getId()])) {
                        unset($remainingTypes[$type->getId()]);
                    }
                }
            }

            if (empty($remainingTypes)) {
                $this->flashBag->add('error', 'flash.admin.transactional.mail.customize.customizableByType.empty.error');

                return new RedirectResponse($this->router->generate('admin_event_transactional_mail_list', [
                    'event' => $event->getId(),
                ]));
            }
        } else {
            $messages = $this->messageRepository->findByEventAndType($event, $transactionalMailType);

            if (!empty($messages)) {
                $this->flashBag->add('error', 'flash.admin.transactional.mail.customize.notCustomizableByType.error');

                return new RedirectResponse($this->router->generate('admin_event_transactional_mail_list', [
                    'event' => $event->getId(),
                ]));
            }
        }

        $customize = new Customize(
            $event,
            $transactionalMailType,
            $data,
            $this->getTemplateTranslations($event, $transactionalMailType)
        );

        $form = $this->formFactory->create(CustomizeType::class, $customize, [
            'isCustomizableByType' => $data['isCustomizableByType'],
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'remainingTypes' => $remainingTypes,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($customize);

            return new RedirectResponse($this->router->generate('admin_event_transactional_mail_list', [
                'event' => $event->getId(),
            ]));
        }

        return new Response($this->twig->render('AdminBundle:Transactional/Mail:customize.html.twig', [
            'form' => $form->createView(),
            'transactionalMailType' => $transactionalMailType,
            'event' => $event,
            'availableParameters' => array_merge(
                Constant::TRANSACTIONAL_MAIL_GENERIC_PARAMETERS,
                $data['isCustomizableByType'] ? Constant::TRANSACTIONAL_MAIL_GENERIC_CUSTOMIZABLE_BY_TYPE_PARAMETERS : [],
                $data['availableParameters']
            )
        ]));
    }

    /**
     * Prepare the translations of the template by locale to populate the form with the generics values
     *
     * @param Event  $event
     * @param string $transactionalMailType
     *
     * @return array indexed by locale with translation of subject and content
     */
    private function getTemplateTranslations(Event $event, string $transactionalMailType): array
    {
        $templateTranslations = [];

        foreach ($event->getLocales() as $locale) {
            $templateTranslations[$locale] = [
                'subject' => $this->translator->trans(
                    Constant::TRANSACTIONAL_MAIL_LIST[$transactionalMailType]['subject'],
                    [],
                    'mail',
                    $locale
                ),
                'content' => $this->twig->render(
                    Constant::TRANSACTIONAL_MAIL_LIST[$transactionalMailType]['template_full_text'],
                    ['locale' => $locale]
                )
            ];
        }

        return $templateTranslations;
    }
}
