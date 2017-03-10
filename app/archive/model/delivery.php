<?php
class archive_mdl_delivery extends dbeav_model{
    var $has_many = array(
        'delivery_items' => 'delivery_items',
        'delivery_order' => 'delivery_order',

    );


    

}

?>