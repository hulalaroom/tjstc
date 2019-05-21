<?php

class HtModel extends RelationModel {
 //合同预约登记
    protected $_auto = array(
        array('syf','strip_tags', 3, 'function'),
		 array('swlx','strip_tags', 3, 'function'),
		 array('cnxs','strip_tags', 3, 'function'),
		 array('jfmj','strip_tags', 3, 'function'),
		 array('yfqz','trim', 3, 'function'),
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
