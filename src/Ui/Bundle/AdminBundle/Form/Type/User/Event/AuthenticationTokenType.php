<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\Event;

use Proximum\Vimeet\Application\Command\User\Event\AuthenticationTokenImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthenticationTokenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AuthenticationTokenImport::class,
        ]);
    }
}
