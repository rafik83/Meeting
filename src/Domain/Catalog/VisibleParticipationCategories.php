<?php

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class VisibleParticipationCategories
{
    /** @var RuleRepositoryInterface */
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
     * @return Category[]
     */
    public function getAllowedCategoriesList(Sheet $sheet): array
    {
        $visibleCategories = [];
        $filteredRules     = [];
        $categories        = $sheet->getType()->getCategories();
        $rules             = $this->ruleRepository->getByEvent($sheet->getEvent());

        foreach ($categories as $category) {
            $filteredRules = array_filter($rules, function (Rule $rule) use ($category) {
                return $rule->getSeerCategory() === $category;
            });
        }

        foreach ($filteredRules as $rule) {
            if (null !== $rule->getSeeableCategory()) {
                $visibleCategories[$rule->getSeeableCategory()->getId()] = $rule->getSeeableCategory();
            }
        }

        return $visibleCategories;
    }
}
