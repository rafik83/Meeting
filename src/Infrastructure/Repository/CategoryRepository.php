<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @param EntityManager           $entityManager
     * @param Paginator               $paginator
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(
        EntityManager $entityManager,
        Paginator $paginator,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->entityManager  = $entityManager;
        $this->paginator      = $paginator;
        $this->typeRepository = $typeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush($category);

        foreach ($category->getTypes() as $type) {
            $this->entityManager->flush($type);
        }

        foreach ($category->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(Category $category)
    {
        $this->entityManager->flush($category);

        foreach ($category->getTypes() as $type) {
            $this->entityManager->flush($type);
        }

        foreach ($category->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($page, $limit, $eventId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\CategoryListView(category.id, translation.title)')
            ->from('Entity:Category', 'category', 'category.id')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->join('category.event', 'event', 'WITH', 'event.id = :eventId')
            ->setParameter('locale', $locale)
            ->setParameter('eventId', $eventId)
            ->orderBy('category.id', 'ASC');

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'category', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryViewsByEventAndUser(Event $event, User $user, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('DISTINCT NEW Proximum\Vimeet\Domain\View\CategoryView(category.id, translation.title)')
            ->from('Entity:Category', 'category')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->join('category.types', 'type', 'WITH', 'type IN (:seeableType)')
            ->setParameter('seeableType', $this->typeRepository->getSeeableTypeIdsByUser($user))
            ->where('category.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('category')
            ->from('Entity:Category', 'category')
            ->where('category.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    public function eventHasCategories(Event $event): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('category.id')
            ->from('Entity:Category', 'category')
            ->where('category.event = :event')
            ->setMaxResults(1)
            ->setParameter('event', $event);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesByEventAndLocale(Event $event, string $locale): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('category, translation')
            ->from('Entity:Category', 'category')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('category.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryViewById($id, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\CategoryView(category.id, translation.title)')
            ->from('Entity:Category', 'category')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('category.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesTitleByEventAndLocale(Event $event, string $locale, array $categories): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('category.id, translations.title')
            ->from('Entity:Category', 'category', 'category.id')
            ->join('category.translations', 'translations', 'WITH', 'category.event = :event AND translations.locale = :locale')
            ->setParameter('event', $event)
            ->setParameter('locale', $locale)
        ;

        if (!empty($categories)) {
            $queryBuilder->andWhere('category IN (:categories)')->setParameter('categories', $categories);
        }

        return array_map(
            function ($category) {
                return $category['title'];
            },
            $queryBuilder->getQuery()->getResult()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFromSheetMeetingRequests(Sheet $sheet, string $locale): array
    {
        // retrieve
        $query = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('category', 'translations')
            ->from('Entity:Category', 'category')
            ->where(
                'category in (
                        SELECT tmpCategory.id
                        FROM Entity:Meeting\Request meetingRequest
                            INNER JOIN meetingRequest.from sheet WITH sheet = :sheet AND meetingRequest.disabled = false
                            INNER JOIN meetingRequest.to toSheet
                            INNER JOIN toSheet.type type
                            INNER JOIN type.categories tmpCategory
            )'
            )
            ->join('category.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('sheet', $sheet)
            ->setParameter('locale', $locale)
            ->getQuery();

        $categories = $query->getResult();

        $query = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('category', 'translations')
            ->from('Entity:Category', 'category')
            ->where(
                'category in (
                        SELECT tmpCategory.id
                        FROM Entity:Meeting\Request meetingRequest
                            INNER JOIN meetingRequest.to sheet WITH sheet = :sheet and meetingRequest.disabled = false
                            INNER JOIN meetingRequest.from fromSheet
                            INNER JOIN fromSheet.type type
                            INNER JOIN type.categories tmpCategory
                )'
            )
            ->join('category.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('sheet', $sheet)
            ->setParameter('locale', $locale)
            ->getQuery();

        $otherSideCategories = $query->getResult();

        // merge
        foreach ($otherSideCategories as $category) {
            if (false === in_array($category, $categories, true)) {
                $categories[] = $category;
            }
        }

        // sort
        usort(
            $categories,
            function (Category $categoryA, Category $categoryB) use ($locale) {
                return $categoryA->getTitle($locale) <=> $categoryB->getTitle($locale);
            }
        );

        return $categories;
    }
}
