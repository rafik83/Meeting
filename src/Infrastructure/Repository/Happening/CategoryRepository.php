<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Happening;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\View\Happening\CategoryListView;

class CategoryRepository implements CategoryRepositoryInterface
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
    public function add(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush($category);
    }

    /**
     * @param Category $category
     */
    public function set(Category $category)
    {
        $this->entityManager->flush($category);

        foreach ($category->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder();

        $this->addSelectReturnNewCategoryListView($queryBuilder);

        $queryBuilder
            ->from(Category::class, 'category')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->where('category.event = :event')
            ->orderBy('category.rank')
            ->setParameter('locale', $locale)
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function getCategoryListViewByType(Type $type, string $locale): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder();

        $this->addSelectReturnNewCategoryListView($queryBuilder);

        $queryBuilder
            ->from(Category::class, 'category')
            ->join(Happening::class, 'happening', 'WITH', 'category.event = :event AND happening.category = category')
            ->join('happening.types', 'type', 'WITH', 'type = :type')
            ->join('category.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->groupBy('category.id')
            ->orderBy('category.rank')
            ->setParameters(
                [
                    'event' => $type->getEvent(),
                    'type' => $type,
                    'locale' => $locale,
                ]
            )
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    private function addSelectReturnNewCategoryListView(QueryBuilder $queryBuilder): QueryBuilder
    {
        return $queryBuilder->select(
            sprintf(
                'NEW %s(category.id, translation.title, category.picto, category.rank, category.leftColor, category.rightColor)',
                CategoryListView::class
            )
        );
    }
}
