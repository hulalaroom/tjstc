<?php

class UserMessageModel extends RelationModel {

    protected $_link = array(
        'Message' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Message',
            'foreign_key' => 'mid',
            'mapping_name' => 'Message',
        ),
          'User' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'User',
            'foreign_key' => 'fromid',
            'mapping_name' => 'User',
        ),

    );
}

?>
