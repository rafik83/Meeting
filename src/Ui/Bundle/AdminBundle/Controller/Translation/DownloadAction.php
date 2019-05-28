<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Translation;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadAction
{
    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        JobQueueInterface $jobQueue,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->jobQueue = $jobQueue;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Access denied');
        }

        $this->jobQueue->downloadTranslations($adminDomain->getAdmin()->getEmail(), $request->getLocale());

        $this->flashBag->add('success', 'flash.admin.translations.download.prepare');

        return new RedirectResponse($this->router->generate('admin_event_list'));
    }
}
