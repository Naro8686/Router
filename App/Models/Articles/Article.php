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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
    /**
     * @return int
     */
    public function getAuthorId(): int
    {
        return (int) $this->authorId;
    }
    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return User::getById($this->authorId);
    }

    protected static function getTableName(): string
    {
        return 'articles';
    }
    public function setName(string $name){
        return $this->name =$name;
    }
    public function setText(string $text){
        return $this->text =$text;
    }

}