<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Db/Table/Select/TestCommon.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class Whitewashing_Db_Table_Select_MysqlTest extends Zend_Db_Table_Select_TestCommon
{
    protected function _setUpAdapter()
    {
        $this->_db = new Whitewashing_Db_Adapter_Mysql($this->_util->getParams());
        try {
            $conn = $this->_db->getConnection();
        } catch (Zend_Exception $e) {
            $this->_db = null;
            $this->assertType('Zend_Db_Adapter_Exception', $e,
                'Expecting Zend_Db_Adapter_Exception, got ' . get_class($e));
            $this->markTestSkipped($e->getMessage());
        }
    }
    
    public function getDriver()
    {
        return 'Mysqli';
    }

}
