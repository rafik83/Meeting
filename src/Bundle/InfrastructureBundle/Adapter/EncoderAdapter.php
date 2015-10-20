<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Application\Adapter\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface as SymfonyPasswordEncoderInterface;

class EncoderAdapter implements PasswordEncoderInterface
{
    /**
     * @var SymfonyPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param SymfonyPasswordEncoderInterface $encoder
     */
    public function __construct(SymfonyPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($password, $salt)
    {
        return $this->encoder->encodePassword($password, $salt);
    }
}
