<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipRepository implements TipRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    /** @var Paginator */
    private $paginator;

    /**
     * SpotUnavailabilityRepository constructor.
     *
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator     = $paginator;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip')
            ->from(Tip::class, 'tip')
            ->where('tip.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($page, $limit = 20)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip', 'translation')
            ->from(Tip::class, 'tip', 'tip.id')
            ->leftJoin('tip.translations', 'translation')
            ->where('tip.event IS NULL')
            ->orderBy('tip.title');

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'tip');
    }

    /**
     * {@inheritdoc}
     */
    public function add(Tip $tip)
    {
        $this->entityManager->persist($tip);

        foreach ($tip->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(Tip $tip)
    {
        $this->entityManager->flush($tip);
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(TipTranslation $translation)
    {
        $this->entityManager->remove($translation);
    }

    /**
     * {@inheritdoc}
     */
    public function removeTip(Tip $tip)
    {
        $this->entityManager->remove($tip);
        $this->entityManager->flush($tip);
    }

    /**
     * {@inheritdoc}
     */
    public function getByContextAndEventAndType(Event $event, Type $type, $context, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'new %s(tip.id, tipTranslation.title, tipTranslation.content, tip.title, tip.display, tip.conditionHasCart, tip.conditionHasRemainingToPay, tip.conditionIsPhoneConfirmed, tip.conditionIsCompleteSheet, tip.conditionHasPendingMeetingProposition, tip.conditionOnOrders)',
                    TipTranslationView::class
                )
            )
            ->from(Tip::class, 'tip')
            ->join('tip.translations', 'tipTranslation', 'WITH', sprintf('tip.%s = true AND tipTranslation.locale = :locale', $context))
            ->join('tip.types', 'type', 'WITH', 'tip.event = :event and type = :type')
            ->orderBy('tip.createdAt')
            ->setParameter('locale', $locale)
            ->setParameter('event', $event)
            ->setParameter('type', $type);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isConfirmationPhoneEnabled(Event $event, Type $type): bool
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('tip')
            ->from(Tip::class, 'tip')
            ->join('tip.types', 'type', 'WITH', 'type.event = :event AND type = :type')
            ->where('tip.onConfirmationPhone = true')
            ->setMaxResults(1)
            ->setParameter('event', $event)
            ->setParameter('type', $type)
        ;

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventAndTip(Event $event, Tip $tip)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip, type')
            ->from(Tip::class, 'tip')
            ->join('tip.types', 'type', 'WITH', 'type.event = :event AND tip = :tip')
            ->setParameter('event', $event)
            ->setParameter('tip', $tip);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function paginateByEvent(Event $event, $page, $limit)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip, type')
            ->from(Tip::class, 'tip', 'tip.id')
            ->join('tip.event', 'event', 'WITH', 'event = :event')
            ->leftJoin('tip.types', 'type')
            ->setParameter('event', $event);

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'tip');
    }

    /**
     * {@inheritdoc}
     */
    public function getTipWithoutEventWithType(): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip')
            ->from(Tip::class, 'tip')
            ->join('tip.types', 'type', 'WITH', 'tip.event IS NULL')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals(): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip')
            ->from(Tip::class, 'tip')
            ->where('tip.event IS NULL')
            ->orderBy('tip.title', 'ASC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip, tipTranslation')
            ->from(Tip::class, 'tip')
            ->join('tip.translations', 'tipTranslation', 'WITH', 'tipTranslation.tip = tip')
            ->orderBy('tip.title');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isTipAffectedToEvent(Tip $tip, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip.id')
            ->from(Tip::class, 'tip')
            ->join('tip.types', 'type', 'WITH', 'type.event = :event AND tip = :tip')
            ->setParameter('event', $event)
            ->setParameter('tip', $tip)
            ->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /** {@inheritdoc} */
    public function getByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip, type')
            ->from(Tip::class, 'tip')
            ->join('tip.types', 'type', 'WITH', 'type.event = :event')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
