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
     * @param string $dateTime
     * @param string $inputTimezone
     * @param string $outputTimezone
     *
     * @return \DateTimeInterface
     */
    public function date($dateTime, $inputTimezone, $outputTimezone)
    {
        return (new \DateTime($dateTime, new \DateTimeZone($inputTimezone)))
            ->setTimezone(new \DateTimeZone($outputTimezone));
    }
}
