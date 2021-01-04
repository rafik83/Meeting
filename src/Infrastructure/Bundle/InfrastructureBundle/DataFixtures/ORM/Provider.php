<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\DataFixtures\ORM;

class Provider
{
    /**
     * @var string
     */
    private $domain;

    /**
     * @param string $domain
     */
    public function __construct(
        string $domain
    ) {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function domain(): string
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
