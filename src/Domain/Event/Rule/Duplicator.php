<?php

namespace Proximum\Vimeet\Domain\Event\Rule;

use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class Duplicator
{
    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var DuplicatorDataStorage */
    private $duplicatorDataStorage;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage)
    {
        $this->duplicatorDataStorage = $duplicatorDataStorage;
        $rules = $this->ruleRepository->getByEvent($event->getDuplicatedFrom());

        foreach ($rules as $rule) {
            $seer    = $this->getDataStorageByWho($rule->getSeer())[$rule->getSeer()->getId()];
            $seeable = $this->getDataStorageByWho($rule->getSeeable())[$rule->getSeeable()->getId()];

            $newRule = new Rule($event, $seer, $seeable, $rule->getWhat(), $rule->getPriority());
            $this->ruleRepository->add($newRule);
        }
    }

    /**
     * @param WhoInterface $who
     *
     * @throws \InvalidArgumentException
     *
     * @return Type[]|Category[]
     */
    private function getDataStorageByWho(WhoInterface $who): array
    {
        if ($who instanceof Type) {
            return $this->duplicatorDataStorage->types;
        } elseif ($who instanceof Category) {
            return $this->duplicatorDataStorage->categories;
        }

        throw new \InvalidArgumentException('Given object must be a type or a category');
    }
}
