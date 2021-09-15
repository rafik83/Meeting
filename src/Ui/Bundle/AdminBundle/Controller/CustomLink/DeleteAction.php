<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\CustomLink;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Intention\IntentionType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DeleteAction
{
    private FlashBagInterface $flashBag;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private UrlGeneratorInterface $urlGenerator;
    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    private CustomLinkRepositoryInterface $customLinkRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        UrlGeneratorInterface $urlGenerator,
        CustomLinkRepositoryInterface $staticFormulationRepository,
        CsrfTokenManagerInterface $csrfTokenManager,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->flashBag = $flashBag;
        $this->customLinkRepository = $staticFormulationRepository;
    }

    public function __invoke(Request $request, Event $event, Event\CustomLink $customLink): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $customLink->getEvent() !== $event
        ) {
            throw new AccessDeniedException();
        }

        $csrfToken = new CsrfToken(IntentionType::INTENTION_REMOVE_CUSTOM_LINK, $request->get('_token'));

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new AccessDeniedException('Invalid csrf token');
        }

        $this->customLinkRepository->remove($customLink);
        $this->flashBag->add('success', 'flash.admin.custom_links.removed.success');

        return new RedirectResponse(
            $this->urlGenerator->generate(
                'admin_event_custom_links_list',
                [
                    'event' => $event->getId(),
                ]
            )
        );
    }
}
