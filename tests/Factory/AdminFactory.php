<?php

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Admin;

class AdminFactory
{
    /**
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     *
     * @return Admin
     */
    public static function create($email = 'admin@vimeet.events', $firstname = 'john', $lastname = 'doe')
    {
        $now = new \DateTime();

        return new Admin($email, 'salt', 'password', 'fr', $firstname, $lastname, 'ROLE_ADMIN', $now);
    }
}
