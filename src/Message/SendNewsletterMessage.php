<?php

namespace App\Message;

final class SendNewsletterMessage
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */

    private $userId;
    private $newsId;
    private $articleId;

    /**
     * @param int $userId "Id de l\'utilisateur"
     * @param int $newsId "Id de la newsletter"
     * @param int $articleId "Id de la nouvelle article publiée"
     */
    public function __construct(int $userId, int $newsId, int $articleId)
    {
        $this->userId = $userId;
        $this->newsId = $newsId;
        $this->articleId = $articleId;
    }



    /**
     * Get the value of userId
     */ 
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the value of newsId
     */ 
    public function getNewsId()
    {
        return $this->newsId;
    }

    /**
     * Get the value of articleId
     */ 
    public function getArticleId()
    {
        return $this->articleId;
    }
}
