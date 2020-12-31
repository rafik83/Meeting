<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Group;

use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\SheetGroup\SheetGroupConstant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupChoiceType extends AbstractType
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * GroupChoiceType constructor.
     *
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setDefaults([
            'choices' => function (Options $options) {
                $groups = $this->groupRepository->getByEvent($options['event']);

                return array_merge(
                    [SheetGroupConstant::UNASSIGNED_GROUP],
                    $groups
                );
            },
            'choice_label' => function ($group) {
                if ($group instanceof Group) {
                    return $group->getTitle();
                }

                if (SheetGroupConstant::UNASSIGNED_GROUP === $group) {
                    return 'form.sheet.sheetGroup.unAssignedGroup';
                }

                return null;
            },
            'choice_value' => function ($group) {
                if ($group instanceof Group) {
                    return $group->getId();
                }

                if (SheetGroupConstant::UNASSIGNED_GROUP === $group) {
                    return SheetGroupConstant::UNASSIGNED_GROUP;
                }

                return null;
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
