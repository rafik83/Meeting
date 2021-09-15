<?php

namespace Proximum\Vimeet\Behat\Proxy\Template;

use Proximum\Vimeet\Behat\Context\Domain\Proxy\Template\RegistrationTemplateContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\RegistrationTemplateManager;

class RegistrationTemplateContextProxy implements RegistrationTemplateContextProxyInterface
{
    /** @var StorageInterface */
    private $storage;

    /** @var RegistrationTemplateManager */
    private $registrationTemplateManager;

    public function __construct(StorageInterface $storage, RegistrationTemplateManager $registrationTemplateManager)
    {
        $this->storage = $storage;
        $this->registrationTemplateManager = $registrationTemplateManager;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getRegistrationTemplateManager(): RegistrationTemplateManager
    {
        return $this->registrationTemplateManager;
    }
}
