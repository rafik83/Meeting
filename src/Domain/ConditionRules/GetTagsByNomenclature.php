<?php

namespace Proximum\Vimeet\Domain\ConditionRules;

use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Domain\Catalog\View\NomenclatureFilterView;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\Model\Event;

class GetTagsByNomenclature
{
    /** @var TaggedNomenclatureFilterGetter */
    private $taggedNomenclatureFilterGetter;

    public function __construct(TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter)
    {
        $this->taggedNomenclatureFilterGetter = $taggedNomenclatureFilterGetter;
    }

    public function __invoke(Field $field, Event $event, string $locale): array
    {
        $tags = [];
        $fields = explode('.', $field->getField());
        $nomenclatureId = (int) end($fields);

        $nomenclatureFilterViews = $this->taggedNomenclatureFilterGetter->getNomenclaturesItemsByEvent(
            $event,
            $locale
        );

        /** @var NomenclatureFilterView $nomenclatureFilterView */
        foreach ($nomenclatureFilterViews as $id => $nomenclatureFilterView) {
            if ($nomenclatureId === $id) {
                $tags = array_merge($tags, $nomenclatureFilterView->tags);
            }
        }

        return $tags;
    }
}
