<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class CreateRequestHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * CreateRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
        $this->dateTime          = $dateTime;
    }

    /**
     * @param CreateRequest $createRequest
     *
     * @return CreateRequestResult
     */
    public function handle(CreateRequest $createRequest)
    {
        // Create new request
        $request = new Request(
            $createRequest->from,
            $createRequest->participants,
            $createRequest->to,
            [],
            $this->dateTime,
            $createRequest->creator
        );

        $this->requestRepository->add($request);

        // Add message
        if ($createRequest->description) {
            $this->messageRepository->add(new Message(
                $request,
                $request->getFromSheet(),
                $createRequest->description,
                $this->dateTime
            ));
        }

        return new CreateRequestResult($request);
    }
}
