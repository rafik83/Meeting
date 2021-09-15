<?php

namespace Proximum\Vimeet\Application\View\User;

class UserView
{
    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $email;

    /**
     * UserView constructor.
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     */
    public function __construct($firstname, $lastname, $email)
    {
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->email     = $email;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}
