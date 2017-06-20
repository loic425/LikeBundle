<?php

/*
 * This file is part of the Like package.
 *
 * (c) Loïc Frémont
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loic425\Sylius\Bundle\LikeBundle\Updater;

use Doctrine\Common\Persistence\ObjectManager;
use Loic425\Component\Like\Model\DislikableInterface;
use Loic425\Component\Like\Model\LikableInterface;
use Loic425\Component\Like\Calculator\LikeCountCalculatorInterface;

/**
 * @author Loïc Frémont <loic@mobizel.com>
 */
class LikeCountUpdater
{
    /**
     * @var LikeCountCalculatorInterface
     */
    private $likeCountCalculator;

    /**
     * @var ObjectManager
     */
    private $likeSubjectManager;

    /**
     * RecalculateLikeCountListener constructor.
     *
     * @param LikeCountCalculatorInterface $likeCountCalculator
     * @param ObjectManager $likeSubjectManager
     */
    public function __construct(LikeCountCalculatorInterface $likeCountCalculator, ObjectManager $likeSubjectManager)
    {
        $this->likeCountCalculator = $likeCountCalculator;
        $this->likeSubjectManager = $likeSubjectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function update(LikableInterface $likeSubject)
    {
        $likeSubject
            ->setLikeCount($this->likeCountCalculator->calculateLikeCount($likeSubject));

        if ($likeSubject instanceof DislikableInterface) {
            $likeSubject
                ->setDislikeCount($this->likeCountCalculator->calculateDislikeCount($likeSubject));
        }

        $this->likeSubjectManager->flush();
    }
}
