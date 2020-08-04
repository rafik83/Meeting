<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Aggregate;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Reconciliate;
use Proximum\Vimeet\Application\Query\Happening\Admin\DownloadWebinarQuery;
use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\File\FileTemporary;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadWebinarAction
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
        Request $request,
        Event $event,
        Happening $happening
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $happening->getEvent()
        ) {
            throw new AccessDeniedException('Access Denied!');
        }

        // get file from remote storage, if already generated
        $downloadWebinar = new DownloadWebinarQuery($happening, $request->query->get('reset') === '1');
        /** @var FileTemporary|null */
        $recordedArchive = $this->queryBus->handle($downloadWebinar);

        if (null === $recordedArchive) {
            // launch reconciliate, to be sure all urls are up to date
            $this->commandBus->handle(new Reconciliate($happening));
            // aggregate files in a zip if webinar has been recorded in multiple archives
            $recordedArchive = $this->commandBus->handle(new Aggregate($happening));
        }

        $response = new BinaryFileResponse($recordedArchive->getTempFilePath());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $recordedArchive->getOriginalName());
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
