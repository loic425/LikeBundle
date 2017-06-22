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

use Loic425\Bundle\LikeBundle\EventListener\RecalculateLikeCountListener;
use Loic425\Bundle\LikeBundle\Form\Type\LikeType;
use Loic425\Bundle\LikeBundle\Updater\LikeCountUpdater;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

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
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $this->registerResources('loic425', $config['driver'], $this->resolveResources($config['resources'], $container), $container);

        $loader->load('services.xml');

        $loader->load(sprintf('integrations/%s.xml', $config['driver']));
    }

    /**
     * {@inheritdoc}
     */
    private function resolveResources(array $resources, ContainerBuilder $container)
    {
        $container->setParameter('loic425.like.subjects', $resources);

        $this->createLikeListeners(array_keys($resources), $container);

        $resolvedResources = [];
        foreach ($resources as $subjectName => $subjectConfig) {
            foreach ($subjectConfig as $resourceName => $resourceConfig) {
                if (is_array($resourceConfig)) {
                    $resolvedResources[$subjectName . '_' . $resourceName] = $resourceConfig;
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
        foreach ($reviewSubjects as $likeSubject) {
            $likeChangeListener = new Definition(RecalculateLikeCountListener::class, [
                new Reference(sprintf('loic425.%s_like.like_count_updater', $likeSubject)),
            ]);

            // recalculate like count on post update
            $likeChangeListener->addTag('kernel.event_listener', [
                'event' => sprintf('loic425.%s_like.post_update', $likeSubject),
                'method' => 'recalculateLikeCount',
            ]);

            // recalculate like count on post delete
            $likeChangeListener->addTag('kernel.event_listener', [
                'event' => sprintf('loic425.%s_like.post_delete', $likeSubject),
                'method' => 'recalculateLikeCount',
            ]);

            // Defines validation groups of the form type
            $container->setParameter(sprintf('loic425.form.type.%s_like.validation_groups', $likeSubject), ['loic425']);

            // Defines form type's service
            $container->setDefinition(sprintf('loic425.form.type.%s_like', $likeSubject), new Definition(LikeType::class, [
                new Parameter(sprintf('loic425.model.%s_like.class', $likeSubject)),
                new Parameter(sprintf('loic425.form.type.%s_like.validation_groups', $likeSubject)),
            ]))->addTag('form.type');

            $container->addDefinitions([
                sprintf('loic425.%s_like.like_count_updater', $likeSubject) => new Definition(LikeCountUpdater::class, [
                    new Reference('loic425.like_count_calculator'),
                    new Reference(sprintf('loic425.manager.%s_like', $likeSubject)),
                ]),
                sprintf('loic425.listener.%s_like_change', $likeSubject) => $likeChangeListener
            ]);
        }
    }
}
