<?php

namespace Proximum\Vimeet\Domain\Repository\Type;

use Proximum\Vimeet\Domain\Model\Type;

interface ContentRepositoryInterface
{
    public function add(Type\Content $content): void;
    public function update(Type\Content $content): void;

    public function findByTypeAndAssociatedParticipationType(string $type, Type $associatedParticipationType): ?Type\Content;

    /**
     * @param string $type
     * @param Type[] $associatedParticipationTypes
     *
     * @return array of $associatedParticipationType id that have content
     *
     * Example of result:
     * [
     *     [
     *         'contentId' => 1,
     *         'associatedParticipationTypeId' => 123,
     *     ]
     *     [
     *         'contentId' => 2,
     *         'associatedParticipationTypeId' => 124,
     *     ]
     * ]
     */
    public function hasContentByAssociatedTypes(string $type, array $associatedParticipationTypes): array;

    public function remove(Type\Content $content): void;
}
