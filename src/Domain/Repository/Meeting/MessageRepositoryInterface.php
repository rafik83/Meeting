<?php

namespace Proximum\Vimeet\Domain\Repository\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

interface MessageRepositoryInterface
{
    /**
     * @param Message $message
     *
     * @return mixed
     */
    public function add(Message $message);

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getLastMessageByRequest(Request $request);

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getMessagesByMeetingRequest(Request $request);

    /**
     * @param int[] $requestIds
     *
     * @return Message[]
     */
    public function getUpdateOrDeleteReasonMessageFromRequestIds(array $requestIds): array;
}
