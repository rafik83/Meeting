<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Template;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;

class SheetTemplateRepository implements SheetTemplateRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SheetTemplateRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(SheetTemplate::class, 'template');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateForGivenEvents(array $events)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(SheetTemplate::class, 'template')
            ->join('template.event', 'event', 'WITH', 'event.id IN (:events)')
            ->setParameter('events', array_map(function (EventInterface $event) { return $event->getId(); }, $events));

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateForGivenEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(SheetTemplate::class, 'template')
            ->where('template.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTemplates()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(SheetTemplate::class, 'template')
            ->where('template.event IS NULL');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizerTemplates(array $events, array $filters)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(SheetTemplate::class, 'template');

        if (isset($filters['event']) && $filters['event'] instanceof EventInterface) {
            $queryBuilder
                ->join('template.event', 'event', 'WITH', 'event.id = :event')
                ->setParameter('event', $filters['event']->getId());
        } else {
            $queryBuilder
                ->join('template.event', 'event', 'WITH', 'event.id IN (:events)')
                ->setParameter('events', array_map(function (EventInterface $event) { return $event->getId(); }, $events));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(SheetTemplate $template)
    {
        $this->entityManager->persist($template);
        $this->entityManager->flush($template);
    }

    /**
     * {@inheritdoc}
     */
    public function set(SheetTemplate $template)
    {
        $this->entityManager->flush($template);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(SheetTemplate::class, 'template')
            ->where('template.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedTemplateForGivenEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(SheetTemplate::class, 'template')
            ->join('template.types', 'type', 'WITH', 'template.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }
}
