<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Group;

use Proximum\Vimeet\Application\Command\Group\Participant\UpdateUsersSheets;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersSheetsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var UpdateUsersSheets $updateUsersSheets */
        $updateUsersSheets = $options['updateUsersSheets'];

        foreach ($updateUsersSheets->sheetsByUser as $userId => $value) {
            $builder
                ->add($userId, SheetChoiceType::class, [
                    'attr'     => ['class' => 'select2 form-control'],
                    'group'    => $options['group'],
                    'multiple' => true,
                    'label'    => false,
                    'required' => false,
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['group', 'updateUsersSheets']);
        $resolver->setAllowedTypes('group', Group::class);
        $resolver->setAllowedTypes('updateUsersSheets', UpdateUsersSheets::class);
        $resolver->setDefaults(['data_class' => UpdateUsersSheets::class]);
    }
}
