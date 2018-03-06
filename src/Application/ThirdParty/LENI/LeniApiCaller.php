<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class LeniApiCaller
{
    private const CONTENT_TYPE = 'application/json';
    private const CLOSE_CONNECTION = 'Close';

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var string */
    private $body;

    /** @var array */
    private $headers;

    /** @var Event */
    private $event;

    /** @var mixed */
    private $data;

    /** @var ExtraParameter */
    private $leniUserParameter;

    /** @var ExtraParameter */
    private $leniEventParameter;

    /** @var ExtraParameter */
    private $leniModeParameter;

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
     * @param Event $event
     * @param mixed $data
     *
     * @return Object
     *
     * @throws \LogicException
     * @throws LeniApiServerException
     */
    public function save(Event $event, mixed $data): Object
    {
        $authorizedModes = [Type::TYPE_LENI_MODE_SAVE_VALUE, Type::TYPE_LENI_MODE_BOTH_VALUE];
        $this->init($event, $data)->prepareParameters()->checkMode($authorizedModes)->prepareBodyAndHeaders();

        try {
            $jsonResponse = $this->httpAdapter->post(LeniConstants::LENI_SAVE_ENDPOINT, $this->headers, $this->body);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        return json_decode($jsonResponse->body, true);
    }

    /**
     * @param Event $event
     * @param mixed $data
     *
     * @return Object
     *
     * @throws \LogicException
     * @throws LeniApiServerException
     */
    public function get(Event $event, mixed $data): Object
    {
        $authorizedModes = [Type::TYPE_LENI_MODE_GET_VALUE, Type::TYPE_LENI_MODE_BOTH_VALUE];
        $this->init($event, $data)->prepareParameters()->checkMode($authorizedModes)->prepareBodyAndHeaders();

        try {
            $jsonResponse = $this->httpAdapter->get(LeniConstants::LENI_GET_ENDPOINT, $this->headers, $this->body);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        return json_decode($jsonResponse->body, true);
    }

    private function prepareBodyAndHeaders(): void
    {
        $this->body = json_encode(
            [
                'idEvt'  => $this->leniEventParameter->getValue(),
                'idUser' => $this->leniUserParameter->getValue(),
                'mode'   => LeniConstants::LENI_MODE,
                'app'    => LeniConstants::LENI_APP,
                'data'   => $this->data
            ]
        );

        $this->headers = [
            'Authorization'  => 'Basic ' . base64_encode($this->leniUserParameter->getValue()),
            'Host'           => LeniConstants::LENI_HOST,
            'Content-Type'   => self::CONTENT_TYPE,
            'Content-Length' => mb_strlen($this->body),
            'Connection'     => self::CLOSE_CONNECTION,
        ];
    }

    private function prepareParameters(): self
    {
        $this->leniUserParameter  = $this->extraParameterRepository
            ->findByEventAndType($this->event, Type::TYPE_LENI_USER);
        $this->leniEventParameter = $this->extraParameterRepository
            ->findByEventAndType($this->event, Type::TYPE_LENI_EVENT);
        $this->leniModeParameter = $this->extraParameterRepository
            ->findByEventAndType($this->event, Type::TYPE_LENI_EVENT);

        return $this;
    }

    /**
     * @throws \LogicException
     */
    private function checkMode(array $authorizedModes): self
    {
        $isAuthorizedMode = $this->leniModeParameter !== null
                            && \in_array($this->leniModeParameter, $authorizedModes, true);

        if (!$isAuthorizedMode || null === $this->leniUserParameter || null === $this->leniEventParameter) {
            throw new \LogicException(
                'Can not call PrepareLeniApiCallHandler if event has not LENI_USER and LENI_EVENT'
            );
        }

        return $this;
    }

    public function init(Event $event, mixed $data): self
    {
        $this->event = $event;
        $this->data  = $data;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): array
    {
        return $this->headers;
    }
}
