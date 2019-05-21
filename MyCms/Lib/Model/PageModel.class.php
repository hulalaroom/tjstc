<?php

class PageModel extends RelationModel {

    protected $_link = array(
        'Cate' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Cate',
            'foreign_key' => 'id',
            'mapping_name' => 'Cate',
        ),

    );

}

?>
