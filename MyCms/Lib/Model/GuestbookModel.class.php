<?php

class GuestbookModel extends RelationModel {
 //Ĭ���Զ���ɹ���
    protected $_auto = array(
        array('summary','strip_tags', 3, 'function'),
		 array('title','strip_tags', 3, 'function'),
		 array('linkman','strip_tags', 3, 'function'),
		 array('phone','strip_tags', 3, 'function'),
		 array('summary','trim', 3, 'function'),
		 array('title','trim', 3, 'function'),
		 array('linkman','trim', 3, 'function'),
		 array('phone','trim', 3, 'function'),
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
