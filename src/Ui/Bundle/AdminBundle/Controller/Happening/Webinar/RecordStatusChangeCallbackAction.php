<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\StatusChangeCallback;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordStatusChangeCallbackAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var string */
    private $apiKey;

    public function __construct(
        CommandBusInterface $commandBus,
        string $apiKey
    ) {
        $this->commandBus = $commandBus;
        $this->apiKey = $apiKey;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $archiveId = $content['id'] ?? null;
        $sessionId = $content['sessionId'] ?? null;
        $status = $content['status'] ?? null;
        $url = $content['url'] ?? null;
        $partnerId = $content['partnerId'] ?? null;

        if ((string) $partnerId !== $this->apiKey) {
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
