<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\TypePriorityView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class TypePriorityViewQueryHandler
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var array
     */
    private $typeAssignedById = [];

    /**
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param TypePriorityViewQuery $query
     *
     * @return TypePriorityView[]
     */
    public function handle(TypePriorityViewQuery $query)
    {
        $this->assignedTypeToId($query);
        $priorityViews = [];
        $rules         = $this->ruleRepository->getByEvent($query->event);

        usort($rules, function (Rule $ruleA, Rule $ruleB) {
            return $ruleA->getPriority() > $ruleB->getPriority();
        });

        foreach ($rules as $rule) {
            $seer        = $rule->getSeer();
            $seeable     = $rule->getSeeable();
            $seerView    = null;
            $seeableView = null;

            if ($seer instanceof Type) {
                $seerView = $this->typeAssignedById[$seer->getId()];
            } elseif ($seer instanceof Category) {
                $seerView = array_map(function (Type $type) {
                    return $this->typeAssignedById[$type->getId()];
                }, $seer->getTypes());
            }

            if ($seeable instanceof Type) {
                $seeableView = $this->typeAssignedById[$seeable->getId()];
            } elseif ($seeable instanceof Category) {
                $seeableView = array_map(function (Type $type) {
                    return $this->typeAssignedById[$type->getId()];
                }, $seeable->getTypes());
            }

            if ($seerView instanceof TypeView && $seeableView instanceof TypeView) {
                if (!$this->hasRuleAlreadyCreated($priorityViews, $seerView, $seeableView)) {
                    $priorityViews[] = new TypePriorityView(
                        $seerView,
                        $seeableView,
                        $rule->getPriority()
                    );
                }
            } elseif (is_array($seerView) && $seeableView instanceof TypeView) {
                foreach ($seerView as $seerSingleView) {
                    if (!$this->hasRuleAlreadyCreated($priorityViews, $seerSingleView, $seeableView)) {
                        $priorityViews[] = new TypePriorityView(
                            $seerSingleView,
                            $seeableView,
                            $rule->getPriority()
                        );
                    }
                }
            } elseif (is_array($seeableView) && $seerView instanceof TypeView) {
                foreach ($seeableView as $seeableSingleView) {
                    if (!$this->hasRuleAlreadyCreated($priorityViews, $seerView, $seeableSingleView)) {
                        $priorityViews[] = new TypePriorityView(
                            $seerView,
                            $seeableSingleView,
                            $rule->getPriority()
                        );
                    }
                }
            } elseif (is_array($seerView) && is_array($seeableView)) {
                foreach ($seerView as $seerSingleView) {
                    foreach ($seeableView as $seeableSingleView) {
                        if (!$this->hasRuleAlreadyCreated($priorityViews, $seerSingleView, $seeableSingleView)) {
                            $priorityViews[] = new TypePriorityView(
                                $seerSingleView,
                                $seeableSingleView,
                                $rule->getPriority()
                            );
                        }
                    }
                }
            }
        }

        return $priorityViews;
    }

    /**
     * @param TypePriorityViewQuery $query
     */
    private function assignedTypeToId(TypePriorityViewQuery $query)
    {
        foreach ($query->types as $type) {
            $this->typeAssignedById[$type->id] = $type;
        }
    }

    /**
     * @param TypePriorityView[] $priorityViews
     * @param TypeView           $seer
     * @param TypeView           $seeable
     *
     * @return bool
     */
    private function hasRuleAlreadyCreated(array $priorityViews, TypeView $seer, TypeView $seeable)
    {
        /** @var TypePriorityView $priorityView */
        foreach ($priorityViews as $priorityView) {
            if ($priorityView->fromType === $seer && $priorityView->toType === $seeable) {
                return true;
            }
        }

        return false;
    }
}
