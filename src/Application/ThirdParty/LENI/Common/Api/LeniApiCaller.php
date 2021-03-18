<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class LeniApiCaller
{
    private const CONTENT_TYPE_APPLICATION_JSON = 'application/json';
    private const CLOSE_CONNECTION = 'Close';

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->extraParameterRepository = $extraParameterRepository;
    }

    /**
     * @throws \LogicException
     * @throws LeniApiServerException
     * @throws NotValidApiCallException
     */
    public function save(Event $event, array $data): array
    {
        $leniUserParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);
        $leniModeParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_MODE);
        $leniEndpointParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_LENI_SAVE_ENDPOINT
        );

        $isAuthorizedMode = null !== $leniModeParameter
            && \in_array($leniModeParameter->getValue(), Type::ALLOWED_LENI_MODE_FOR_SAVE, true)
        ;

        if (!$isAuthorizedMode
            || null === $leniUserParameter
            || null === $leniEventParameter
            || null === $leniEndpointParameter
        ) {
            throw new \LogicException(
                'Can not call LeniApiCaller because event has not LENI_USER, LENI_EVENT, valid LENI_MODE or LENI Save endpoint'
            );
        }

        $body = json_encode(
            [
                'idEvt' => $leniEventParameter->getValue(),
                'idUser' => $leniUserParameter->getValue(),
                'mode' => LeniConstants::LENI_MODE,
                'app' => LeniConstants::LENI_APP,
                'data' => $data,
            ]
        );

        $headers = $this->getHeaders($leniUserParameter->getValue(), $body);

        try {
            $jsonResponse = $this->httpAdapter->post($leniEndpointParameter->getValue(), $headers, $body);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        $response = json_decode($jsonResponse->body, true);

        if (!isset($response[LeniConstants::LENI_IS_VALID]) || true !== $response[LeniConstants::LENI_IS_VALID]) {
            throw new NotValidApiCallException(sprintf(
                "Headers: %s;\nRequest: %s;\nResponse: %s;",
                json_encode($headers),
                $body,
                $jsonResponse->body
            ));
        }

        return $response;
    }

    /**
     * @param Event $event
     * @param array $fields
     * @param array $filters
     * @param array $sort
     * @param int   $start
     * @param int   $limit
     *
     * @throws LeniApiServerException
     *
     * @return array
     */
    public function get(
        Event $event,
        array $fields,
        array $filters,
        array $sort,
        int $start,
        int $limit
    ): array {
        $leniUserParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);
        $leniModeParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_MODE);
        $leniEndpointParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_LENI_GET_ENDPOINT
        );

        $isAuthorizedMode = null !== $leniModeParameter
            && \in_array($leniModeParameter->getValue(), Type::ALLOWED_LENI_MODE_FOR_GET, true)
        ;

        if (!$isAuthorizedMode
            || null === $leniUserParameter
            || null === $leniEventParameter
            || null === $leniEndpointParameter
        ) {
            throw new \LogicException(
                'Can not call LeniApiCaller because event has not LENI_USER, LENI_EVENT, valid LENI_MODE or LENI Get endpoint'
            );
        }

        $body = $this->getGetCallBody($leniEventParameter->getValue(), $fields, $filters, $sort, $start, $limit);
        $jsonEncodedBody = json_encode($body);
        $headers = $this->getHeaders($leniUserParameter->getValue(), $jsonEncodedBody);

        try {
            $jsonResponse = $this->httpAdapter->post($leniEndpointParameter->getValue(), $headers, $jsonEncodedBody);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        $response = json_decode($jsonResponse->body, true);

        return $response[LeniConstants::LENI_RESULTS] ?? [];
    }

    /**
     * @param string $idEvt
     * @param array  $fields
     * @param array  $filters
     * @param array  $sort
     * @param int    $start
     * @param int    $limit
     *
     * @return array
     */
    private function getGetCallBody(
        string $idEvt,
        array $fields,
        array $filters,
        array $sort,
        int $start,
        int $limit
    ): array {
        return [
            'idEvt' => $idEvt,
            'filters' => $filters,
            'fields' => $fields,
            'start' => $start,
            'take' => $limit,
            'sort' => $sort,
        ];
    }

    private function getHeaders(string $authorizedUser, string $body): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($authorizedUser),
            'Host' => LeniConstants::LENI_HOST,
            'Content-Type' => self::CONTENT_TYPE_APPLICATION_JSON,
            'Content-Length' => mb_strlen($body),
            'Connection' => self::CLOSE_CONNECTION,
        ];
    }
}
