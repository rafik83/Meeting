<?php

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Catalog\View\NomenclatureFilterView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class TaggedNomenclatureFilterGetter
{
    /** @var TaggedNomenclatureFilterRepositoryInterface */
    private $taggedNomenclatureFilterRepository;

    /** @var NomenclatureRepositoryInterface */
    private $nomenclatureRepository;

    /**
     * @param TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository
     * @param NomenclatureRepositoryInterface             $nomenclatureRepository
     */
    public function __construct(
        TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository,
        NomenclatureRepositoryInterface $nomenclatureRepository
    ) {
        $this->taggedNomenclatureFilterRepository = $taggedNomenclatureFilterRepository;
        $this->nomenclatureRepository             = $nomenclatureRepository;
    }

    /**
     * @param Event  $event
     * @param string $tag
     * @param string $locale
     *
     * @return array
     */
    public function getNomenclaturesItems(Event $event, $tag, $locale)
    {
        $taggedNomenclatureFilter = $this->taggedNomenclatureFilterRepository->getByEventAndTag(
            $event,
            $tag
        );

        $nomenclatureIds = null === $taggedNomenclatureFilter ? [] : $taggedNomenclatureFilter->getNomenclaturesId();

        $nomenclatures = $this->nomenclatureRepository->findByEventAndIds(
            $event,
            $nomenclatureIds
        );

        $nomenclatureItems = [];

        foreach ($nomenclatures as $nomenclature) {
            $nomenclatureLabelsIndexedByKey = $this->buildNomenclature($nomenclature->getChildren(), $locale);

            foreach ($nomenclatureLabelsIndexedByKey as $key => $label) {
                $nomenclatureItems[$key] = $label;
            }
        }

        asort($nomenclatureItems);

        return $nomenclatureItems;
    }

    public function getNomenclaturesItemsByEvent(Event $event, string $locale): array
    {
        $taggedNomenclatureFilters = $this->taggedNomenclatureFilterRepository->getByEvent($event);
        $tagsByNomenclatureId = [];

        foreach ($taggedNomenclatureFilters as $taggedNomenclatureFilter) {
            foreach ($taggedNomenclatureFilter->getNomenclaturesId() as $nomenclatureId) {
                $tagsByNomenclatureId[$nomenclatureId][] = $taggedNomenclatureFilter->getTag();
            }
        }

        $nomenclatures = $this->nomenclatureRepository->findByEventAndIds(
            $event,
            array_keys($tagsByNomenclatureId)
        );

        $nomenclatureItems = [];
        $availableLocale = $event->getAvailableLocale($locale);

        foreach ($nomenclatures as $nomenclature) {
            $nomenclatureId = $nomenclature->getId();
            $nomenclatureItems[$nomenclatureId] = new NomenclatureFilterView(
                $nomenclatureId,
                $nomenclature->getTitle(),
                $this->buildNomenclature($nomenclature->getLastLevel(), $availableLocale),
                $tagsByNomenclatureId[$nomenclature->getId()] ?? []
            );
        }

        return $nomenclatureItems;
    }

    /**
     * @param Event  $event
     * @param string $tag
     * @param string $locale
     *
     * @return NomenclaturesItemsView
     */
    public function getLastNomenclaturesItems(Event $event, $tag, $locale): NomenclaturesItemsView
    {
        $taggedNomenclatureFilter = $this->taggedNomenclatureFilterRepository->getByEventAndTag(
            $event,
            $tag
        );

        if (null === $taggedNomenclatureFilter) {
            return new NomenclaturesItemsView([], 0);
        }

        $nomenclatures = $this->nomenclatureRepository->findByEventAndIds(
            $event,
            $taggedNomenclatureFilter->getNomenclaturesId()
        );

        $nomenclatureItems = [];

        foreach ($nomenclatures as $nomenclature) {
            $nomenclatureLabelsIndexedByKey = $this->buildNomenclature($nomenclature->getLastLevel(), $locale);

            foreach ($nomenclatureLabelsIndexedByKey as $key => $label) {
                $nomenclatureItems[$key] = $label;
            }
        }

        return new NomenclaturesItemsView($nomenclatureItems, $this->getMaxDepth($nomenclatures));
    }

    /**
     * @param Nomenclature[] $nomenclatures
     *
     * @return int
     */
    private function getMaxDepth(array $nomenclatures): int
    {
        $maxDepth = 0;

        foreach ($nomenclatures as $nomenclature) {
            if ($maxDepth < $nomenclature->getDepth()) {
                $maxDepth = $nomenclature->getDepth();
            }
        }

        return $maxDepth;
    }

    /**
     * @param string             $locale
     * @param NomenclatureItem[] $nomenclatureItems
     *
     * @return array
     */
    private function buildNomenclature($nomenclatureItems, $locale)
    {
        $buildItems = [];

        foreach ($nomenclatureItems as $nomenclatureItem) {
            $buildItems[$nomenclatureItem->getKey()] = $nomenclatureItem->getLabel($locale);
        }

        return $buildItems;
    }
}
