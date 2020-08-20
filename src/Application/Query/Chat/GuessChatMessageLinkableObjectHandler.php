<?php

namespace Proximum\Vimeet\Application\Query\Chat;

use Proximum\Vimeet\Application\Exception\Happening\HappeningNotFoundException;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class GuessChatMessageLinkableObjectHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        HappeningRepositoryInterface $happeningRepository
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->happeningRepository = $happeningRepository;
    }

    /**
     * @throws HappeningNotFoundException
     */
    public function handle(GuessChatMessageLinkableObject $query): ChatMessageLinkableInterface
    {
        if ('happening' === $query->objectType) {
            $happening = $this->happeningRepository->findById($query->objectId);

            if (null === $happening) {
                throw new HappeningNotFoundException('Happening not found for given id.');
            }

            return $happening;
        }

        if ('meeting' === $query->objectType) {
            $meeting = $this->meetingRepository->findById($query->objectId);

            if (null === $meeting) {
                throw new HappeningNotFoundException('Meeting not found for given id.');
            }

            return $meeting;
        }

        throw new \InvalidArgumentException('Invalid ObjectType.');
    }
}
