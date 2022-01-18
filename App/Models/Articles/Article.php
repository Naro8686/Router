<?php

namespace App\Models\Articles;

use App\Models\ActiveRecordEntity;
use App\Models\Users\User;

class Article extends ActiveRecordEntity
{

    /** @var string */
    protected $name;

    /** @var string */
    protected $text;

    /** @var string */
    protected $authorId;

    /** @var string */
    protected $createdAt;


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
    /**
     * @return int
     */
    public function getAuthorId()
    {
        return (int) $this->authorId;
    }
    /**
     * @return User
     */
    public function getAuthor()
    {
        return User::getById($this->authorId);
    }

    public static function getTableName()
    {
        return 'articles';
    }
    public function setName($name){
        return $this->name =$name;
    }
    public function setText($text){
        return $this->text =$text;
    }

}