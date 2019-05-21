<?php

class OwnerModel extends RelationModel {
 //个人信息
    protected $_auto = array(
         array('linkman','strip_tags', 3, 'function'),
		 array('linktel','strip_tags', 3, 'function'),
		 array('status','trim', 3, 'function'),
    );

    protected $_link = array(

          'User' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'User',
            'foreign_key' => 'userid',
            'mapping_name' => 'User',
        ),
               'Cate' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Cate',
            'foreign_key' => 'cat_id',
            'mapping_name' => 'Cate',
        ),

    );
}

?>
