<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Adapter\ThirdParty\Jenkins;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Infrastructure\Adapter\ExecAdapter;
use Proximum\Vimeet\Infrastructure\Adapter\ThirdParty\Jenkins\BuildCreatorAdapter;

class BuildCreatorAdapterTest extends TestCase
{
    public function testCreate()
    {
        $execAdapter = $this->prophesize(ExecAdapter::class);
        $user = 'jenkins';
        $password = 'jenkinsP';
        $jenkinsCommand = 'curl -v -X POST http://optaplanner:8080/job/%buildName%/build --user %jenkinsUser%:%jenkinsPassword% --data-urlencode json=\'\'{"parameter": %jenkinsParameters%}\'\'';

        $arguments = [
            'Param1' => 'Value1',
            'Param2' => 'Value2',
        ];

        $output = [];
        $result = 0;
        $command = "curl -v -X POST http://optaplanner:8080/job/ThisIsATest/build --user jenkins:jenkinsP --data-urlencode json=''{\"parameter\": [{\"name\":\"Param1\",\"value\":\"Value1\"},{\"name\":\"Param2\",\"value\":\"Value2\"}]}'' 2>&1";

        $execAdapter->exec(
            $command,
            $output,
            $result
        )->shouldBeCalled();

        $buildCreatorAdapter = new BuildCreatorAdapter(
            $execAdapter->reveal(),
            $jenkinsCommand,
            $user,
            $password
        );
        $buildCreatorAdapter->create('ThisIsATest', $arguments);
    }
}
