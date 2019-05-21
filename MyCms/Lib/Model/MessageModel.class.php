<?php

class MessageModel extends RelationModel {

    protected $_link = array(
        'UserMessage' => array(
            'mapping_type' => HAS_MANY,
            'class_name' => 'UserMessage',
            'foreign_key' => 'mid',
            'mapping_name' => 'UserMessage',
        ),
     
    );

}

?>
