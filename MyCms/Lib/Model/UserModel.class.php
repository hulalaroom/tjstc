<?php

class UserModel extends RelationModel {

    protected $_link = array(
        'Group' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Group',
            'foreign_key' => 'group_id',
            'mapping_name' => 'Group',
        ),

    );

}

?>
