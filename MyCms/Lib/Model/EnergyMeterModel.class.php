<?php

class EnergyMeterModel extends RelationModel {

    protected $_link = array(
        'User' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'User',
            'foreign_key' => 'uid',
            'mapping_name' => 'User',
        ),

    );

}

?>
