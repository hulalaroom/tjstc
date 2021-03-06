<?php

class RepairModel extends RelationModel {
 //终端设备维修预约登记
    protected $_auto = array(
		 array('RepairMan','strip_tags', 3, 'function'),
		 array('RepairTel','strip_tags', 3, 'function'),
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
