<?php

/*
 * This file is part of the Like package.
 *
 * (c) Loïc Frémont
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loic425\Bundle\LikeBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @author Loïc Frémont <loic@mobizel.com>
 */
class Loic425LikeExtension extends AbstractResourceExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $config);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->registerResources('sylius', $config['driver'], $this->resolveResources($config['resources'], $container), $container);

        $loader->load('services.xml');

        $loader->load(sprintf('integrations/%s.xml', $config['driver']));
    }

    /**
     * {@inheritdoc}
     */
    private function resolveResources(array $resources, ContainerBuilder $container)
    {
        $container->setParameter('sylius.like.subjects', $resources);

        //$this->createLikeListeners(array_keys($resources), $container);

        $resolvedResources = [];
        foreach ($resources as $subjectName => $subjectConfig) {
            foreach ($subjectConfig as $resourceName => $resourceConfig) {
                if (is_array($resourceConfig)) {
                    $resolvedResources[$subjectName.'_'.$resourceName] = $resourceConfig;
                }
            }
        }

        return $resolvedResources;
    }

    /**
     * @param array $reviewSubjects
     * @param ContainerBuilder $container
     */
    private function createLikeListeners(array $reviewSubjects, ContainerBuilder $container)
    {
        foreach ($reviewSubjects as $reviewSubject) {
            $reviewChangeListener = new Definition(ReviewChangeListener::class, [
                new Reference(sprintf('sylius.%s_review.average_rating_updater', $reviewSubject)),
            ]);

            $reviewChangeListener->addTag('kernel.event_listener', [
                'event' => sprintf('sylius.%s_review.post_update', $reviewSubject),
                'method' => 'recalculateSubjectRating',
            ]);
            $reviewChangeListener->addTag('kernel.event_listener', [
                'event' => sprintf('sylius.%s_review.post_delete', $reviewSubject),
                'method' => 'recalculateSubjectRating',
            ]);

            $container->addDefinitions([
                sprintf('sylius.%s_review.average_rating_updater', $reviewSubject) => new Definition(AverageRatingUpdater::class, [
                    new Reference('sylius.average_rating_calculator'),
                    new Reference(sprintf('sylius.manager.%s_review', $reviewSubject)),
                ]),
                sprintf('sylius.listener.%s_review_change', $reviewSubject) => $reviewChangeListener,
            ]);
        }
    }
}
