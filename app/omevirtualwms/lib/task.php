<?php
/*

*/

class omevirtualwms_task{

    function week(){

    }

    function minute(){

    }

    function hour(){
        app::get('omevirtualwms')->model('dataStatus')->scanANDclean();
    }

    function day(){
        
    }

    function month(){

    }

}
