<?php

namespace App\Message;

final class NewsletterUserMessage
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */

    private $userId;

    /**
     * @param int $userId "Id de l\'utilisateur"
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }



    /**
     * Get the value of userId
     */ 
    public function getUserId()
    {
        return $this->userId;
    }

}
