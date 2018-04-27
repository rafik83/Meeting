<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\DataFixtures\ORM;

class Provider
{
    /**
     * @var string
     */
    private $domain;

    /**
     * @param $domain
     */
    public function __construct($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function domain()
    {
        return $this->domain;
    }

    /**
     * @param string $datetime
     * @param string $inputTimezone
     * @param string $outputTimezone
     *
     * @return \DateTimeInterface
     */
    public function date($datetime, $inputTimezone, $outputTimezone)
    {
        return (new \DateTime($datetime, new \DateTimeZone($inputTimezone)))
            ->setTimezone(new \DateTimeZone($outputTimezone));
    }
}
