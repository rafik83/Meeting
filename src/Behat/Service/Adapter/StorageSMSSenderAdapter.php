<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Adapter;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;

class StorageSMSSenderAdapter implements SMSSenderInterface
{
    const MESSAGE_LOGGED = 'SMS sent to %s with message: %s';

    /** @var StorageInterface */
    private $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        var_dump('init StorageSMSSenderAdapter');
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function send(SMS $sms)
    {
        $this->storage->set(
            'sms_sent',
            sprintf(self::MESSAGE_LOGGED, $sms->getReceiver(), $sms->getMessage())
        );
    }
}
