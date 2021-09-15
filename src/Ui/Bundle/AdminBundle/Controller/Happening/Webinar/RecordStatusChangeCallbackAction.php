<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\StatusChangeCallback;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordStatusChangeCallbackAction
{
    private CommandBusInterface $commandBus;
    private VideoConferenceAdapterInterface $videoConferenceAdapter;

    public function __construct(
        CommandBusInterface $commandBus,
        VideoConferenceAdapterInterface $videoConferenceAdapter
    ) {
        $this->commandBus = $commandBus;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $archiveId = $content['id'] ?? null;
        $sessionId = $content['sessionId'] ?? null;
        $status = $content['status'] ?? null;
        $url = $content['url'] ?? null;
        $partnerId = $content['partnerId'] ?? null;

        if (!$this->videoConferenceAdapter->checkApiKey((string) $partnerId)) {
            return new JsonResponse(
                ['message' => 'wrong parameters'],
                Response::HTTP_FORBIDDEN
            );
        }

        if (null === $sessionId || null === $archiveId || null === $status) {
            return new JsonResponse(
                ['message' => 'missing parameters'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->commandBus->handle(new StatusChangeCallback(
            $archiveId,
            $sessionId,
            $status,
            $url
        ));

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }
}
