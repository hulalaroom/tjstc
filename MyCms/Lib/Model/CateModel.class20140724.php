<?php

class CateModel extends RelationModel {
	
    protected $_link = array(
        'Model' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Model',
            'foreign_key' => 'mid',
            'mapping_name' => 'Model',
        ),
        'Page' => array(
            'mapping_type' => HAS_ONE,
            'class_name' => 'Page',
            'foreign_key' => 'id',
            'mapping_name' => 'Page',
        ),
        'Article' => array(
            'mapping_type' => HAS_MANY,
            'class_name' => 'Article',
            'foreign_key' => 'cat_id',
            'mapping_name' => 'Article',
        ),
        'Business' => array(
            'mapping_type' => HAS_MANY,
            'class_name' => 'Business',
            'foreign_key' => 'cat_id',
            'mapping_name' => 'Business',
        ),
        'Guestbook' => array(
            'mapping_type' => HAS_MANY,
            'class_name' => 'Guestbook',
            'foreign_key' => 'cat_id',
            'mapping_name' => 'Guestbook',
        ),
        'Vote' => array(
            'mapping_type' => HAS_MANY,
            'class_name' => 'Vote',
            'foreign_key' => 'cat_id',
            'mapping_name' => 'Vote',
        ),
    );

}

?>
