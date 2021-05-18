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
    public static function create($email = null, int $id = 1)
    {
        $email = null === $email ? 'email@email.com' : $email;

        // p@ssw0rd
        $user = new User($email, '0D/UTZan1ZbStvnSEBj6flRGgQ59fyeSV9dnIT+5', 'q5sEATy5kfjoDYZqxBP7vNVJwqQ=', 'fr');

        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, $id);
        $property->setAccessible(false);

        return $user;
    }

    public static function createWithEmptyPassword(string $email): User
    {
        return new User($email, '', '', 'fr');
    }
}
