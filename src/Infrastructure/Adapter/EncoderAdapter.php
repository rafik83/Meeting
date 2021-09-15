<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EncoderAdapter implements PasswordEncoderInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($user, $password)
    {
        return $this->encoder->encodePassword($user, $password);
    }
}
