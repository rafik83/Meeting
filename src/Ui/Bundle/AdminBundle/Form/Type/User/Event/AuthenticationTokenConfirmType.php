<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\Event;

use Proximum\Vimeet\Application\Command\User\Event\ConfirmAuthenticationTokenImport;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthenticationTokenConfirmType extends AbstractType
{
    /** @var TranslatorAdapter */
    private $translator;

    public function __construct(TranslatorAdapter $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('submit', SubmitType::class, [
            'label' => 'form.authentication_token.children.submit.label',
            'confirm' => $this->translator->trans(
                'form.authentication_token.import.confirm',
                [],
                'forms',
                $options['locale']
            ),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('locale')
            ->setDefault('data_class', ConfirmAuthenticationTokenImport::class);
    }
}
