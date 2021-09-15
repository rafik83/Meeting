<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\VideoConference;
use Proximum\Vimeet\Domain\Repository\VideoConferenceRepositoryInterface;

class VideoConferenceRepository implements VideoConferenceRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findByMeeting(Meeting $meeting): ?VideoConference
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('video_conference', 'tokens')
            ->from(VideoConference::class, 'video_conference')
            ->leftJoin('video_conference.tokens', 'tokens')
            ->where('video_conference.meeting = :meeting')
            ->setParameter('meeting', $meeting);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(VideoConference $videoConference)
    {
        $this->entityManager->persist($videoConference);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function set(VideoConference $videoConference)
    {
        $this->entityManager->flush($videoConference);
    }
}
