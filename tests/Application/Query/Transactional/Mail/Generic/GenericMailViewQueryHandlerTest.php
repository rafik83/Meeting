<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Transactional\Mail\Generic;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQuery;
use Proximum\Vimeet\Application\Query\Transactional\Mail\Generic\GenericMailViewQueryHandler;
use Proximum\Vimeet\Application\View\Transactional\Mail\Generic\GenericMailView;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class GenericMailViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $key = Constant::TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED;
        $data = Constant::TRANSACTIONAL_MAIL_LIST[$key];

        $query = new GenericMailViewQuery('fr', $key, $data);

        $translator = $this->prophesize(TranslatorInterface::class);

        $translator->trans($data['subject'], [], 'mail', 'fr')
            ->shouldBeCalled()
            ->willReturn('TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED')
        ;

        $handler = new GenericMailViewQueryHandler($translator->reveal());
        $result = $handler->handle($query);

        $expected = new GenericMailView($key, 'TRANSACTIONAL_MAIL_KEY_USER_CHANGED_PASSWORD_CONFIRMED');

        $this->assertEquals($expected, $result);
    }
}
