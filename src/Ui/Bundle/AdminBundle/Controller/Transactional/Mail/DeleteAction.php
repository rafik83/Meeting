<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Transactional\Mail;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Intention\IntentionType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DeleteAction
{
    /** @var FlashBagInterface */
    private $flashBag;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        UrlGeneratorInterface $urlGenerator,
        MessageRepositoryInterface $messageRepository,
        CsrfTokenManagerInterface $csrfTokenManager,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->flashBag = $flashBag;
        $this->messageRepository = $messageRepository;
    }

    public function __invoke(Request $request, Event $event, Message $message): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $message->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $csrfToken = new CsrfToken(IntentionType::INTENTION_REMOVE_CUSTOMIZED_MAIL, $request->get('_token'));

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new AccessDeniedException('Invalid csrf token');
        }

        $this->messageRepository->remove($message);
        $this->flashBag->add('success', 'flash.admin.transactional.mail.remove.success');

        return new RedirectResponse($this->urlGenerator->generate('admin_event_transactional_mail_list', [
            'event' => $event->getId(),
        ]));
    }
}
