<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantRepository implements ParticipantRepositoryInterface
{
    /** @var EntityManager */
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
    public function add(Participant $participant)
    {
        $this->entityManager->persist($participant);
        $this->entityManager->flush($participant);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Participant $participant)
    {
        $this->entityManager->remove($participant);
        $this->entityManager->flush($participant);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Participant $participant)
    {
        $this->entityManager->flush($participant);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->where('participant.id = :id')
            ->join('participant.sheet', 'sheet')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $ids)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet')
            ->where('participant.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantViewsBySheet($sheetId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\ParticipantView(participant.id, participant.data, user.id, user.email, participant.owner)')
            ->from(Participant::class, 'participant')
            ->join('participant.user', 'user')
            ->where('participant.sheet = :sheetId')
            ->setParameter('sheetId', $sheetId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsBySheetId($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.user', 'user')
            ->where('participant.sheet = :sheetId')
            ->setParameter('sheetId', $id);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsBySheetIds(array $ids)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant, user')
            ->from(Participant::class, 'participant')
            ->join('participant.user', 'user', 'WITH', 'participant.sheet IN (:sheetIds)')
            ->setParameter('sheetIds', $ids);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsBySheetIdsWithSheetAndTypeHydrated(array $ids)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant, sheet, type')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.id IN (:ids)')
            ->join('sheet.type', 'type')
            ->setParameter('ids', $ids);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastParticipantIdForEventAndUser($userId, $eventId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant.id')
            ->from(Participant::class, 'participant')
            ->join('participant.user', 'user', 'WITH', 'user.id = :userId')
            ->setParameter('userId', $userId)
            ->join('participant.sheet', 'sheet')
            ->join('sheet.type', 'type')
            ->join('type.event', 'event', 'WITH', 'event.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('participant.id', 'DESC')
            ->setMaxResults(1);

        $result = $queryBuilder->getQuery()->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result ? intval($result) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantForUserAndSheet(User $user, Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->where('participant.user = :userId AND participant.sheet = :sheetId')
            ->setParameter('userId', $user->getId())
            ->setParameter('sheetId', $sheet->getId())
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllParticipantForUser(Event $event, User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'participant.user = :user AND sheet.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsByUserForEvent($userId, EventInterface $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'participant.user = :userId AND sheet.event = :eventId')
            ->setParameter('userId', $userId)
            ->setParameter('eventId', $event->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getInactiveParticipantForSheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->where('participant.sheet = :sheetId')
            ->setParameter('sheetId', $sheet->getId())
            ->andWhere('participant.active = false');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countByEnabledSheet(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(participant)')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.enable = :enable AND sheet.event = :event')
            ->setParameter('event', $event)
            ->setParameter('enable', true);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countByTypeWithEnabledSheet(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(participant) as total, type.id, typeTranslation.title')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.enable = :enable AND sheet.event = :event')
            ->join('sheet.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->groupBy('type')
            ->setParameter('enable', true)
            ->setParameter('event', $event)
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableParticipants(
        array $participants,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        Meeting $exceptedMeeting = null,
        Happening $exceptedHappening = null,
        $exceptAllUnavailabilities = false
    ) {
        if (empty($participants)) {
            return [];
        }

        $firstParticipant = reset($participants);

        if (!$firstParticipant instanceof Participant) {
            return [];
        }

        $eventId = $firstParticipant->getSheet()->getEvent()->getId();

        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.user', 'user', 'WITH', 'participant IN (:participants)')
            ->setParameter('participants', $participants);

        $unavailabilityConditions = '1 = 1';

        if (false === $exceptAllUnavailabilities) {
            // Participant have not unavailability during this period
            $unavailabilityConditions =
                'NOT EXISTS (
                    SELECT u.id
                    FROM Entity:Unavailability u
                    WHERE
                        u.user = user AND u.event = :eventId
                        AND (
                            u.begin >= :begin AND u.begin < :end
                            OR u.end > :begin AND u.end <= :end
                            OR u.begin <= :begin AND u.end >= :end
                        )
                )';
        }

        $queryBuilder->andWhere(
            $queryBuilder->expr()->andX(
                // Participant have not already a meeting during this period
                'NOT EXISTS (
                    SELECT m.id
                    FROM Entity:Meeting m
                    JOIN m.slot slot WITH slot.event = :eventId
                    LEFT JOIN m.fromParticipants fp
                    LEFT JOIN m.toParticipants tp
                    WHERE
                        ' . (null !== $exceptedMeeting ? 'm != :exceptedMeeting' : '1=1') . '
                        AND (fp.user = user OR tp.user = user)
                        AND (
                            slot.begin >= :begin AND slot.begin < :end
                            OR slot.end > :begin AND slot.end <= :end
                            OR slot.begin <= :begin AND slot.end >= :end
                        )
                )',
                // Participant have not happening during this period
                'NOT EXISTS (
                    SELECT hp.id
                    FROM Entity:HappeningParticipation hp
                    JOIN hp.happening h WITH h.event = :eventId
                    WHERE
                        ' . (null !== $exceptedHappening ? 'h != :exceptedHappening' : '1=1') . '
                        AND hp.user = user
                        AND hp.disabled = false
                        AND (
                            h.begin >= :begin AND h.begin < :end
                            OR h.end > :begin AND h.end <= :end
                            OR h.begin <= :begin AND h.end >= :end
                        )
                )',
                $unavailabilityConditions
            )
        );

        if (null !== $exceptedMeeting) {
            $queryBuilder->setParameter('exceptedMeeting', $exceptedMeeting);
        }

        if (null !== $exceptedHappening) {
            $queryBuilder->setParameter('exceptedHappening', $exceptedHappening);
        }

        $queryBuilder
            ->setParameter('eventId', $eventId)
            ->setParameter('begin', $begin)
            ->setParameter('end', $end);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableParticipantsForHappening(array $participants, Happening $happening)
    {
        return $this->getAvailableParticipants(
            $participants,
            $happening->getBegin(),
            $happening->getEnd(),
            null,
            $happening
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsForHappening(Sheet $sheet, Happening $happening)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join(
                HappeningParticipation::class,
                'happeningParticipation',
                'WITH',
                'happeningParticipation.user = participant.user AND happeningParticipation.happening = :happening AND participant.sheet = :sheet AND happeningParticipation.disabled = false'
            )
            ->setParameter('sheet', $sheet)
            ->setParameter('happening', $happening);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant', 'sheet')
            ->from(Participant::class, 'participant')
            ->join('participant.user', 'user')
            ->join('participant.sheet', 'sheet')
            ->join('sheet.event', 'event')
            ->where('sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndInCatalog(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event AND sheet.enable = true AND sheet.inCatalog = true')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventAndSheetIds(Event $event, array $sheetIds): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.id IN (:sheetIds) AND sheet.event = :event')
            ->setParameter('sheetIds', $sheetIds)
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventAndSheetIdsAndLocale(Event $event, array $sheetIds, $locale): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant, sheet, type, typeTranslation')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.id IN (:sheetIds) AND sheet.event = :event')
            ->join('sheet.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('sheetIds', $sheetIds)
            ->setParameter('event', $event)
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventWithoutDispatch(Event $event, Mass $mass)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.user', 'user')
            ->join('participant.sheet', 'sheet')
            ->join('sheet.event', 'event')
            ->where('sheet.event = :event')
            ->setParameter('event', $event)
            ->andWhere('NOT EXISTS (SELECT m.id FROM ' . MassAssignment::class . ' m WHERE m.user = user AND m.mass = :mass)')
            ->setParameter('mass', $mass);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsWithoutMeetingAndHappening(
        array $participants,
        \DateTimeInterface $begin,
        \DateTimeInterface $end
    ) {
        return $this->getAvailableParticipants($participants, $begin, $end, null, null, true);
    }

    public function isAvailableForMeeting(array $participants, Meeting $meeting): bool
    {
        $meetingSlot = $meeting->getSlot();

        return $participants === $this->getParticipantsWithoutMeetingAndHappening(
            $participants,
            $meetingSlot->getBegin(),
            $meetingSlot->getEnd()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.enable = true AND sheet.event = :event')
            ->join('sheet.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('event', $event)
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getParticipantsFromEnabledSheetsByEvent(Event $event, string $locale): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join(
                'participant.sheet',
                'sheet',
                'WITH',
                'sheet.enable = true AND sheet.event = :event AND sheet.state IN (:states)'
            )
            ->join('sheet.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameters([
                'event' => $event,
                'locale' => $locale,
                'states' => [
                    Sheet::STATE_ACCEPTED,
                    Sheet::STATE_VALIDATED,
                ],
            ]);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countParticipantBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(participant)')
            ->from(Participant::class, 'participant')
            ->where('participant.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByGroup(Group $group)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant, user, sheet')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.group = :group AND sheet.enable = true')
            ->join('participant.user', 'user')
            ->setParameter('group', $group)
            ->orderBy('user.account.lastName')
            ->addOrderBy('user.account.firstName');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastEventParticipation(User $user, Event $currentEvent): ?Participant
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event != :event')
            ->join('sheet.event', 'event')
            ->join('event.days', 'day')
            ->where('participant.user = :user')
            ->orderBy('day.startTime', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('event', $currentEvent)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantEmailsForEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('user.email AS email')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->join('participant.user', 'user')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getProductIdsOfUserForEvent(User $user, Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('participantProduct.id')
            ->from(Participant::class, 'participant')
            ->join('participant.sheet', 'sheet', 'WITH', 'sheet.event = :event and participant.user = :user')
            ->join('participant.participantProduct', 'participantProduct')
            ->setParameters([
                'user' => $user,
                'event' => $event,
            ])
            ->groupBy('participantProduct.id')
            ->getQuery()
            ->getResult();
    }

    public function updateAllNetworkingChatViewedAt(User $user, EventInterface $event, DateTimeInterface $date)
    {
        $sheetIds = $this->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user)
            ->andWhere('sheet.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getScalarResult();

        $this->entityManager
            ->createQueryBuilder()
            ->update(Participant::class, 'participant')
            ->set('participant.networkingChatViewedAt', ':date')
            ->setParameter('date', $date)
            ->where('participant.sheet IN (:sheetIds)')
            ->setParameter('sheetIds', $sheetIds)
            ->andWhere('participant.user IN (:user)')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
