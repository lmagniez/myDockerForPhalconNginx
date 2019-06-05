<?php

//namespace ApplicationModels;

class Articles extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $titre;

    /**
     *
     * @var string
     */
    public $contenu;

    /**
     *
     * @var string
     */
    public $date_publication;

    /**
     *
     * @var integer
     */
    public $tagId;

    /**
     *
     * @var integer
     */
    public $userId;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("database");
        $this->setSource("articles");
        $this->hasMany('id', 'ApplicationModels\Comments', 'articleId', ['alias' => 'Comments']);
        $this->belongsTo('tagId', 'ApplicationModels\Tags', 'id', ['alias' => 'Tags']);
        $this->belongsTo('userId', 'ApplicationModels\Users', 'id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'articles';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Articles[]|Articles|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Articles|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
