<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareZipRecordArchive;
use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\Happening\Webinar\IsRecordedFileAccessible;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PrepareDownloadWebinarAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var IsRecordedFileAccessible */
    private $isRecordedFileAccessible;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        IsRecordedFileAccessible $isRecordedFileAccessible,
        RouterInterface $router,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->isRecordedFileAccessible = $isRecordedFileAccessible;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    public function __invoke(
        Request $request,
        Event $event,
        Happening $happening,
        AdminDomain $adminDomain
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $happening->getEvent()
        ) {
            throw new AccessDeniedException('Access Denied!');
        }

        if (!$this->isRecordedFileAccessible->isSatisfiedBy($happening)) {
            $admin = $adminDomain->getAdmin();
            $this->commandBus->handle(
                new PrepareZipRecordArchive(
                    $happening,
                    false,
                    $admin,
                    $request->getLocale()
                )
            );

            $this->flashBag->add('warning', 'flash.admin.happening.webinar.zip_record_archive.not_prepared');

            return new RedirectResponse(
                $this->router->generate('admin_happening_list', ['event' => $event->getId()])
            );
        }

        return new RedirectResponse($happening->getWebinarRecordZipFileUrl());
    }
}
