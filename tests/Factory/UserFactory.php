<?php

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\User;

class UserFactory
{
    /**
     * @param string $email
     *
     * @return User
     */
    public static function create($email = null)
    {
        $email = null === $email ? 'email@email.com' : $email;

        // p@ssw0rd
        return new User($email, '0D/UTZan1ZbStvnSEBj6flRGgQ59fyeSV9dnIT+5', 'q5sEATy5kfjoDYZqxBP7vNVJwqQ=', 'fr');
    }

    public static function createWithEmptyPassword(string $email): User
    {
        return new User($email, '', '', 'fr');
    }
}
