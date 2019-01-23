<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Ovh\Api;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\TwilioClient;
use Proximum\Vimeet\Infrastructure\Adapter\SMSSenderAdapter;

class SMSSenderAdapterTest extends TestCase
{
    /* @todo to rewrite */
}
