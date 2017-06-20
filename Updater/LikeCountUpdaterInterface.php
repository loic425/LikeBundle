<?php

/*
 * This file is part of the Like package.
 *
 * (c) Loïc Frémont
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loic425\Bundle\LikeBundle\Updater;

use Loic425\Component\Like\Model\LikableInterface;

/**
 * @author Loïc Frémont <loic@mobizel.com>
 */
interface LikeCountUpdaterInterface
{
    /**
     * @param LikableInterface $likeSubject
     */
    public function update(LikableInterface $likeSubject);
}
