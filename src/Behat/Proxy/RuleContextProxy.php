<?php

namespace Proximum\Vimeet\Behat\Proxy;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\RuleContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\RuleManager;

class RuleContextProxy implements RuleContextProxyInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var RuleManager
     */
    private $ruleManager;

    /**
     * RuleContextProxy constructor.
     *
     * @param StorageInterface $storage
     * @param RuleManager      $ruleManager
     */
    public function __construct(StorageInterface $storage, RuleManager $ruleManager)
    {
        $this->storage = $storage;
        $this->ruleManager = $ruleManager;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @return RuleManager
     */
    public function getRuleManager(): RuleManager
    {
        return $this->ruleManager;
    }
}
