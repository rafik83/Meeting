<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\Webinar\Download;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\IsRecordedFileAccessibleForUser;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var IsRecordedFileAccessibleForUser */
    private $isRecordedFileAccessibleForUser;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        IsRecordedFileAccessibleForUser $isRecordedFileAccessibleForUser
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->isRecordedFileAccessibleForUser = $isRecordedFileAccessibleForUser;
    }

    public function __invoke(
        EventDomain $eventDomain,
        Happening $happening,
        ?UserDomain $userDomain = null
    ): RedirectResponse {
        $event = $eventDomain->getEvent();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $event)
            || $happening->getEvent() !== $event
            || $userDomain === null
            || $userDomain->getUser() === null
            || !$this->isRecordedFileAccessibleForUser->isSatisfiedBy($happening, $userDomain->getUser())
        ) {
           throw new AccessDeniedException('Access denied');
        }

        return new RedirectResponse($happening->getWebinarRecordZipFileUrl());
    }
}
