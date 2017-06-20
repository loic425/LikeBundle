<?php

/*
 * This file is part of the Like package.
 *
 * (c) Loïc Frémont
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loic425\Bundle\LikeBundle\EventListener;

use Loic425\Bundle\LikeBundle\Updater\LikeCountUpdaterInterface;
use Loic425\Component\Like\Model\LikeInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Loïc Frémont <loic@mobizel.com>
 */
class RecalculateLikeCountListener
{
    /**
     * @var LikeCountUpdaterInterface
     */
    private $likeCountUpdater;

    /**
     * @param GenericEvent $event
     */
    public function recalculateLikeCount(GenericEvent $event)
    {
        $like = $event->getSubject();
        if (!$like instanceof LikeInterface) {
            throw new UnexpectedTypeException($like, LikeInterface::class);
        }

        $this->likeCountUpdater->update($like->getLikeSubject());
    }
}
