<?php

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class VisibleParticipationTypes
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param Sheet $sheet
     *
     * @return Type[]
     */
    public function getAllowedTypesList(Sheet $sheet)
    {
        $visibleTypes = [];
        $type = $sheet->getType();

        $rules = $this->ruleRepository->getByEvent($sheet->getEvent());

        $filteredRules = array_filter($rules, function (Rule $rule) use ($type) {
            return $rule->getSeerType() === $type
                || ($rule->getSeerCategory() !== null && in_array($type, $rule->getSeerCategory()->getTypes(), true));
        });

        foreach ($filteredRules as $rule) {
            if ($rule->getSeeableCategory() !== null) {
                foreach ($rule->getSeeableCategory()->getTypes() as $categoryType) {
                    $visibleTypes[$categoryType->getId()] = $categoryType;
                }
            } else {
                $visibleTypes[$rule->getSeeableType()->getId()] = $rule->getSeeableType();
            }
        }

        return $visibleTypes;
    }
}
