<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FollowerChoiceType extends AbstractType
{
    const UNASSIGNED_FOLLOWER = 'un-assigned-follower';

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * FollowerChoiceType constructor.
     *
     * @param AdminRepositoryInterface $adminRepository
     */
    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setDefault('unassigned', false);
        $resolver->setDefaults([
            'choices'      => function (Options $options) {
                $admins = $this->adminRepository->getFollowers($options['event']);

                if ($options['unassigned'] === true) {
                    return array_merge([self::UNASSIGNED_FOLLOWER], $admins);
                } else {
                    return $admins;
                }
            },
            'choice_label' => function ($admin) {
                if ($admin instanceof Admin) {
                    return $admin->getDisplayName();
                } else {
                    return 'admin.sheet.follower.un-assigned';
                }
            },
            'choice_value' => function ($admin) {
                if ($admin instanceof Admin) {
                    return $admin->getId();
                } elseif ($admin === self::UNASSIGNED_FOLLOWER) {
                    return self::UNASSIGNED_FOLLOWER;
                } else {
                    return null;
                }
            }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
