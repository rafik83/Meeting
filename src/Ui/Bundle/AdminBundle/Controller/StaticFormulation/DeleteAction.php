<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\StaticFormulation;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Intention\IntentionType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
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

    /** @var StaticFormulationRepositoryInterface */
    private $staticFormulationRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        UrlGeneratorInterface $urlGenerator,
        StaticFormulationRepositoryInterface $staticFormulationRepository,
        CsrfTokenManagerInterface $csrfTokenManager,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->flashBag = $flashBag;
        $this->staticFormulationRepository = $staticFormulationRepository;
    }

    public function __invoke(Request $request, Event $event, StaticFormulation $staticFormulation): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $staticFormulation->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $csrfToken = new CsrfToken(IntentionType::INTENTION_REMOVE_STATIC_FORMULATION, $request->get('_token'));

        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new AccessDeniedException('Invalid csrf token');
        }

        $this->staticFormulationRepository->remove($staticFormulation);
        $this->flashBag->add('success', 'flash.admin.staticFormulation.removed.success');

        return new RedirectResponse($this->urlGenerator->generate('admin_event_static_formulation_list', [
            'event' => $event->getId(),
        ]));
    }
}
