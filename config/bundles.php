<?php

return [
    // Symfony
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true],

    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Elao\Bundle\FormTranslationBundle\ElaoFormTranslationBundle::class => ['all' => true],
    FOS\ElasticaBundle\FOSElasticaBundle::class => ['all' => true],
    League\Tactician\Bundle\TacticianBundle::class => ['all' => true],
    Liip\ImagineBundle\LiipImagineBundle::class => ['all' => true],
    Payum\Bundle\PayumBundle\PayumBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Snc\RedisBundle\SncRedisBundle::class => ['all' => true],
    Sonata\IntlBundle\SonataIntlBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle::class => ['dev' => true, 'test' => true],
    Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle::class => ['dev' => true, 'test' => true],
    Hautelook\AliceBundle\HautelookAliceBundle::class => ['dev' => true, 'test' => true],
    FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['test' => true],

    // OAuth2
    KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle::class => ['all' => true],

    // OVH (used to send SMS)
    CoopTilleuls\OvhBundle\CoopTilleulsOvhBundle::class => ['all' => true],

    // Sentry
    Sentry\SentryBundle\SentryBundle::class => ['all' => true],

    // Vimeet
    Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\InfrastructureBundle::class => ['all' => true],
    Proximum\Vimeet\Ui\Bundle\AdminBundle\AdminBundle::class => ['all' => true],
    Proximum\Vimeet\Ui\Bundle\EventBundle\EventBundle::class => ['all' => true],
    Proximum\Vimeet\Ui\Bundle\MailBundle\MailBundle::class => ['all' => true],
];
