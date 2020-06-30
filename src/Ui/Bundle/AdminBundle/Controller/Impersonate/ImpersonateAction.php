<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Impersonate;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CsrfTokenAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\Authorization;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ImpersonateAction
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationCheckerAdapter;

    /** @var CsrfTokenAdapterInterface */
    private $csrfTokenAdapter;

    /** @var Impersonate */
    private $impersonate;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CsrfTokenAdapterInterface $csrfTokenAdapter,
        Impersonate $impersonate,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->csrfTokenAdapter = $csrfTokenAdapter;
        $this->impersonate = $impersonate;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain, Event $event, User $user): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_USER_ACCESS', new Authorization($user, $event))
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_SWITCH')
        ) {
            throw new AccessDeniedException('Access Denied!');
        }

        if (!$this->csrfTokenAdapter->isTokenValid('impersonate-to-'.$user->getId(), $request->request->get('_token'))) {
            throw new BadRequestHttpException('invalid csrf token');
        }

        $targetRoute = $request->query->get('route');
        if (empty($targetRoute)) {
            throw new BadRequestHttpException('route parameter must be defined');
        }

        $targetParams = $request->query->get('params');

        $token = $this->impersonate->getEncodedToken($adminDomain->getAdmin(), $user);
        $targetParams['_switchto'] = $token;
        $targetUrl = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $event,
            $targetRoute,
            $targetParams
        );

        return new RedirectResponse($targetUrl);
    }
}
