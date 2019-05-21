<?php

class VoteModel extends RelationModel {

    protected $_link = array(
        'Cate' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Cate',
            'foreign_key' => 'cat_id',
            'mapping_name' => 'Cate',
        ),
        'Admin' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Admin',
            'foreign_key' => 'admin_id',
            'mapping_name' => 'Admin',
        ),
    );

}

?>
