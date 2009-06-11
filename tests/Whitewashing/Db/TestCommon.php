<?php

set_include_path(
    dirname(__FILE__).'/../../../library/' . PATH_SEPARATOR .
    '/var/www/wsnetbeans/zf/trunk/tests/' . PATH_SEPARATOR .
    get_include_path()
);

/**
 * @see Zend_Db_Adapter_TestCommon
 */
require_once 'Zend/Db/Adapter/TestCommon.php';

/**
 * @see Whitewashing_Db_Adapter_Mysql
 */
require_once 'Whitewashing/Db/Adapter/Mysql.php';