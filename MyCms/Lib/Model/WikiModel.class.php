<?php

class WikiModel extends RelationModel {

    protected $_link = array(
 
        'WikiUser' => array(
            'mapping_type' =>BELONGS_TO,
            'class_name' => 'WikiUser',
            'foreign_key' => 'FromUserName',
            'mapping_name' => 'WikiUser',
        ),
       
    );

}

?>