<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\NomenclatureRepositoryInterface;

class NomenclatureManager
{
    private NomenclatureRepositoryInterface $nomenclatureRepository;
    private TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository;

    public function __construct(
        NomenclatureRepositoryInterface $nomenclatureRepository,
        TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository
    ) {
        $this->nomenclatureRepository = $nomenclatureRepository;
        $this->taggedNomenclatureFilterRepository = $taggedNomenclatureFilterRepository;
    }

    public function find(Event $event, string $title): ?Nomenclature
    {
        $nomenclatures = $this->nomenclatureRepository->findByEvent($event);

        foreach ($nomenclatures as $nomenclature) {
            if ($nomenclature->getTitle() === $title) {
                return $nomenclature;
            }
        }

        return null;
    }

    public function create(?Event $event, string $title, array $values): Nomenclature
    {
        $nomenclature = new Nomenclature($title, 1, $values, true, $event);

        $this->nomenclatureRepository->add($nomenclature);

        return $nomenclature;
    }

    public function createTaggedNomenclatureFilter(Event $event, string $tag, int $nomenclatureId): void
    {
        $taggedNomenclatureFilter = new TaggedNomenclatureFilter($event, $tag, [$nomenclatureId]);
        $this->taggedNomenclatureFilterRepository->add($taggedNomenclatureFilter);
    }
}
