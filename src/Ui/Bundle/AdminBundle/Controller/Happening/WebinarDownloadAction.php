<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Aggregate;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Reconciliate;
use Proximum\Vimeet\Application\Query\Happening\Admin\WebinarDownloadQuery;
use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class WebinarDownloadAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Event $event,
        Happening $happening,
        AdminDomain $adminDomain
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $happening->getEvent()
        ) {
            throw new AccessDeniedException('Access Denied!');
        }

        // launch reconciliate, to be sure all urls are up to date
        $this->commandBus->handle(new Reconciliate($happening));

        // aggregate files in a zip if webinar has been recorded in multiple archives
        $this->commandBus->handle(new Aggregate($happening));

        $recordedArchivePath = $this->queryBus->handle(new WebinarDownloadQuery($happening));

        $recordedArchiveFile = new \SplFileInfo($recordedArchivePath);

        $response = new BinaryFileResponse($recordedArchiveFile->getRealPath());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $recordedArchiveFile->getFilename());

        return $response;
    }
}
