<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Command\Template\Registration\Update;
use Proximum\Vimeet\Application\Command\Template\Registration\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // context
        $dateTime = new \DateTime();
        $array    = [
            '811f6edf' => [
                'component' => "block",
                'type'      => "12",
                'config'    => [
                    'style' => 'style-1',
                ],
                'children'  => [
                    'dded0597' => [
                        'component' => "object",
                        'type'      => "text",
                        'config'     =>[
                            'content' => [
                                'fr' => "Profil"
                            ],
                            'type' => "titre",
                        ],
                    ],
                ],
            ],
        ];
        $registrationTemplate = new RegistrationTemplate('base tata', [], ['fr'], 'fr', $dateTime);
        $update               = new Update($registrationTemplate);
        $update->title        = 'base toto';
        $update->value        = $array;

        // expected
        $expectedTemplate = new RegistrationTemplate('base toto', $array, ['fr'], 'fr', $dateTime);

        // mock
        $registrationTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $registrationTemplateRepository->set($expectedTemplate)->shouldBeCalled();

        $handler = new UpdateHandler($registrationTemplateRepository->reveal());
        $handler->handle($update);
    }
}
