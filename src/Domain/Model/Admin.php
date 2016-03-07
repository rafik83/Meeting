<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

/**
 * "Compte admin/organisateur/collaborateur".
 */
class Admin extends AbstractUser
{
    const ROLE_ORGANIZER   = 'ROLE_ORGANIZER';
    const ROLE_OPERATOR    = 'ROLE_OPERATOR';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $role;

    /**
     * @param string $email
     * @param string $salt
     * @param string $password
     * @param string $locale
     * @param string $firstname
     * @param string $lastname
     * @param string $role
     */
    public function __construct($email, $salt, $password, $locale, $firstname, $lastname, $role)
    {
        parent::__construct($email, $salt, $password, $locale);

        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->role      = $role;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return [$this->getRole()];
    }

    /**
     * @return array
     */
    public function getAllRoles()
    {
        return [
            self::ROLE_STAFF,
            self::ROLE_ORGANIZER,
            self::ROLE_SUPER_ADMIN,
        ];
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }
}
