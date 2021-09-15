<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Stay;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Intention\IntentionType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RemoveAction
{
    /** @var StayRepositoryInterface */
    private $stayRepository;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CsrfTokenManagerInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        StayRepositoryInterface $stayRepository,
        CsrfTokenManagerInterface $csrfTokenManager,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        RouterInterface $router
    ) {
        $this->stayRepository = $stayRepository;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->router = $router;
    }

    public function __invoke(Request $request, User $user, Event $event, Stay $stay): RedirectResponse
    {
        $csrfToken = new CsrfToken(IntentionType::INTENTION_REMOVE_STAY, $request->get('_token'));

        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->csrfTokenManager->isTokenValid($csrfToken)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $this->stayRepository->remove($stay);
        $this->flashBag->add('success', $this->translator->trans('admin.rooming.stay.remove.success'));

        return new RedirectResponse(
            $this->router->generate('admin_event_rooming_list', [
                'event' => $event->getId(),
                '_fragment' => sprintf('user_%d', $user->getId()),
            ])
        );
    }
}
