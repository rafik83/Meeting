<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public static function provideLocales()
    {
        return [
            ['fr', 'Bonjour Maxime'],
            ['en', 'Hello Maxime'],
        ];
    }

    /**
     * @dataProvider provideLocales
     */
    public function testIndex($locale, $contains)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', sprintf('/%s/hello/Maxime', $locale));

        $this->assertTrue($crawler->filter(sprintf('html:contains("%s")', $contains))->count() > 0);
    }
}
