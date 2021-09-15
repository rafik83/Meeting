<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template;

use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationTemplateChoiceType extends AbstractType
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @param RegistrationTemplateRepositoryInterface $templateRepository
     */
    public function __construct(RegistrationTemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('event')
            ->setDefaults([
            'choices'          => function (Options $options) {
                return $this->getResults($options);
            },
            'choice_label'     => 'title',
            'repositoryMethod' => function (RegistrationTemplateRepositoryInterface $templateRepository) {
                return $templateRepository->getBaseTemplates();
            },
            'repositoryMethodEvent' => function (Options $options) {
                return function (RegistrationTemplateRepositoryInterface $templateRepository) use ($options) {
                    return $templateRepository->getTemplateForGivenEvent($options['event']);
                };
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

    /**
     * @param Options $options
     *
     * @return array
     */
    private function getResults(Options $options): array
    {
        $baseTemplates  = $options['repositoryMethod']($this->templateRepository);
        $eventTemplates = $options['repositoryMethodEvent']($this->templateRepository);

        $templates = [];

        if (!empty($baseTemplates)) {
            $templates['form.type_template.registration.base'] = $baseTemplates;
        }

        if (!empty($eventTemplates)) {
            $templates['form.type_template.registration.event'] = $eventTemplates;
        }

        return $templates;
    }
}
