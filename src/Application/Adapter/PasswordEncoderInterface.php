<?php

namespace Proximum\Vimeet\Application\Adapter;

interface PasswordEncoderInterface
{
    /**
     * Encode password.
     *
     * @param mixed  $user
     * @param string $password
     *
     * @return string
     */
    public function encode($user, $password);
}
