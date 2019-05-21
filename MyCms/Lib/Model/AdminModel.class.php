<?php

class AdminModel extends RelationModel {
    protected $_link = array(
        'RoleAdmin' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Role_admin',
            'foreign_key' => 'user_id',
            'mapping_name' => 'RoleAdmin',
        )
    );
}

