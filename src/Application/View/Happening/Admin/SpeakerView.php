<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

class SpeakerView
{
    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct($firstName, $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }
}
