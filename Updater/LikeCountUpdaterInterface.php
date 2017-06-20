<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 18/05/2016
 * Time: 11:57
 */

namespace Loic425\Sylius\Bundle\LikeBundle\Updater;

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
