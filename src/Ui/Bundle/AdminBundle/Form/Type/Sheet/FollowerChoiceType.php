<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Admin\Follower\FollowerConstant;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FollowerChoiceType extends AbstractType
{
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
            'choices' => function (Options $options) {
                $admins = $this->adminRepository->getFollowers($options['event']);

                if (true === $options['unassigned']) {
                    return array_merge([FollowerConstant::UNASSIGNED_FOLLOWER], $admins);
                } else {
                    return $admins;
                }
            },
            'choice_label' => function ($admin) {
                if ($admin instanceof Admin) {
                    return $admin->getDisplayName();
                }

                return 'admin.sheet.follower.un-assigned';
            },
            'choice_value' => function ($admin) {
                if ($admin instanceof Admin) {
                    return $admin->getId();
                } elseif (FollowerConstant::UNASSIGNED_FOLLOWER === $admin) {
                    return FollowerConstant::UNASSIGNED_FOLLOWER;
                } else {
                    return null;
                }
            },
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
