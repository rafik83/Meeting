<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Sonata\IntlBundle\SonataIntlBundle(),
            new Elao\Bundle\FormTranslationBundle\ElaoFormTranslationBundle(),
            new FOS\ElasticaBundle\FOSElasticaBundle(),
            new League\Tactician\Bundle\TacticianBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Payum\Bundle\PayumBundle\PayumBundle(),

            // JMS Job Queue
            new JMS\JobQueueBundle\JMSJobQueueBundle(),

            // Sentry
            new Sentry\SentryBundle\SentryBundle(),

            // OVH
            new CoopTilleuls\OvhBundle\CoopTilleulsOvhBundle(),

            // OAuth2
            new KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle(),

            // Vimeet
            new Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\InfrastructureBundle(),
            new Proximum\Vimeet\Ui\Bundle\EventBundle\EventBundle(),
            new Proximum\Vimeet\Ui\Bundle\AdminBundle\AdminBundle(),
            new Proximum\Vimeet\Ui\Bundle\MailBundle\MailBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();

            // Fixtures
            $bundles[] = new Hautelook\AliceBundle\HautelookAliceBundle();
        }

        if (in_array($this->getEnvironment(), ['prod', 'dev'])) {
            // Redis
            $bundles[] = new Snc\RedisBundle\SncRedisBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
