<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Proxy\Template;

use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Behat\Service\Manager\RegistrationTemplateManager;

interface RegistrationTemplateContextProxyInterface
{
    public function getStorage(): StorageInterface;

    public function getRegistrationTemplateManager(): RegistrationTemplateManager;
}
