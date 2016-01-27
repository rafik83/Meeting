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
     * CreateRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param CreateRequest $createRequest
     */
    public function handle(CreateRequest $createRequest)
    {
        $request = new Request(
            $createRequest->from,
            $createRequest->fromParticipants,
            $createRequest->to,
            [],
            $createRequest->createdAt,
            $createRequest->creator
        );

        $this->requestRepository->add($request);

        // Add message
        $this->messageRepository->add(new Message(
          $request,
          $request->getFromSheet(),
          $createRequest->description,
          $createRequest->createdAt
      ));
    }
}
