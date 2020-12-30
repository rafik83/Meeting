<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Template;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class RegistrationTemplateRepository implements RegistrationTemplateRepositoryInterface
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
    public function getBaseTemplates()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(RegistrationTemplate::class, 'template')
            ->where('template.event IS NULL');

        return $queryBuilder->getQuery()->getResult();
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
            ->from(RegistrationTemplate::class, 'template')
            ->where('template.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllOrganizersTemplates()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(RegistrationTemplate::class, 'template')
            ->where('template.event IS NOT NULL');

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
            ->from(RegistrationTemplate::class, 'template')
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
            ->from(RegistrationTemplate::class, 'template')
            ->where('template.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedTemplateForGivenEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(RegistrationTemplate::class, 'template')
            ->join('template.types', 'type', 'WITH', 'template.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(RegistrationTemplate $registrationTemplate)
    {
        $this->entityManager->persist($registrationTemplate);
        $this->entityManager->flush($registrationTemplate);
    }

    /**
     * {@inheritdoc}
     */
    public function set(RegistrationTemplate $registrationTemplate)
    {
        $this->entityManager->flush($registrationTemplate);
    }
}
