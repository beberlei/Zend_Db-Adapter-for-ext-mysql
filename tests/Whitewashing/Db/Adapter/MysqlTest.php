<?php

require_once dirname(__FILE__)."/../TestCommon.php";


/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Whitewashing_Db_Adapter_MysqlTest extends Zend_Db_Adapter_TestCommon
{
    /**
     * Open a new database connection
     */
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

    protected $_numericDataTypes = array(
        Zend_Db::INT_TYPE    => Zend_Db::INT_TYPE,
        Zend_Db::BIGINT_TYPE => Zend_Db::BIGINT_TYPE,
        Zend_Db::FLOAT_TYPE  => Zend_Db::FLOAT_TYPE,
        'INT'                => Zend_Db::INT_TYPE,
        'INTEGER'            => Zend_Db::INT_TYPE,
        'MEDIUMINT'          => Zend_Db::INT_TYPE,
        'SMALLINT'           => Zend_Db::INT_TYPE,
        'TINYINT'            => Zend_Db::INT_TYPE,
        'BIGINT'             => Zend_Db::BIGINT_TYPE,
        'SERIAL'             => Zend_Db::BIGINT_TYPE,
        'DEC'                => Zend_Db::FLOAT_TYPE,
        'DECIMAL'            => Zend_Db::FLOAT_TYPE,
        'DOUBLE'             => Zend_Db::FLOAT_TYPE,
        'DOUBLE PRECISION'   => Zend_Db::FLOAT_TYPE,
        'FIXED'              => Zend_Db::FLOAT_TYPE,
        'FLOAT'              => Zend_Db::FLOAT_TYPE
    );

    /**
     * Test AUTO_QUOTE_IDENTIFIERS option
     * Case: Zend_Db::AUTO_QUOTE_IDENTIFIERS = true
     *
     * MySQL actually allows delimited identifiers to remain
     * case-insensitive, so this test overrides its parent.
     */
    public function testAdapterAutoQuoteIdentifiersTrue()
    {
        $params = $this->_util->getParams();

        $params['options'] = array(
            Zend_Db::AUTO_QUOTE_IDENTIFIERS => true
        );
        $db = Zend_Db::factory($this->getDriver(), $params);
        $db->getConnection();

        $select = $this->_db->select();
        $select->from('zfproducts');
        $stmt = $this->_db->query($select);
        $result1 = $stmt->fetchAll();
        $this->assertEquals(3, count($result1), 'Expected 3 rows in first query result');

        $this->assertEquals(1, $result1[0]['product_id']);

        $select = $this->_db->select();
        $select->from('zfproducts');
        try {
            $stmt = $this->_db->query($select);
            $result2 = $stmt->fetchAll();
            $this->assertEquals(3, count($result2), 'Expected 3 rows in second query result');
            $this->assertEquals($result1, $result2);
        } catch (Zend_Exception $e) {
            $this->fail('exception caught where none was expected.');
        }
    }

    public function testAdapterInsertSequence()
    {
        $this->markTestSkipped($this->getDriver() . ' does not support sequences');
    }

    /**
     * test that quoteColumnAs() accepts a string
     * and an alias, and returns each as delimited
     * identifiers, with 'AS' in between.
     */
    public function testAdapterQuoteColumnAs()
    {
        $string = "foo";
        $alias = "bar";
        $value = $this->_db->quoteColumnAs($string, $alias);
        $this->assertEquals('`foo` AS `bar`', $value);
    }

    /**
     * test that quoteColumnAs() accepts a string
     * and an alias, but ignores the alias if it is
     * the same as the base identifier in the string.
     */
    public function testAdapterQuoteColumnAsSameString()
    {
        $string = 'foo.bar';
        $alias = 'bar';
        $value = $this->_db->quoteColumnAs($string, $alias);
        $this->assertEquals('`foo`.`bar`', $value);
    }

    /**
     * test that quoteIdentifier() accepts a string
     * and returns a delimited identifier.
     */
    public function testAdapterQuoteIdentifier()
    {
        $value = $this->_db->quoteIdentifier('table_name');
        $this->assertEquals('`table_name`', $value);
    }

    /**
     * test that quoteIdentifier() accepts an array
     * and returns a qualified delimited identifier.
     */
    public function testAdapterQuoteIdentifierArray()
    {
        $array = array('foo', 'bar');
        $value = $this->_db->quoteIdentifier($array);
        $this->assertEquals('`foo`.`bar`', $value);
    }

    /**
     * test that quoteIdentifier() accepts an array
     * containing a Zend_Db_Expr, and returns strings
     * as delimited identifiers, and Exprs as unquoted.
     */
    public function testAdapterQuoteIdentifierArrayDbExpr()
    {
        $expr = new Zend_Db_Expr('*');
        $array = array('foo', $expr);
        $value = $this->_db->quoteIdentifier($array);
        $this->assertEquals('`foo`.*', $value);
    }

    /**
     * test that quoteIdentifer() escapes a double-quote
     * character in a string.
     */
    public function testAdapterQuoteIdentifierDoubleQuote()
    {
        $string = 'table_"_name';
        $value = $this->_db->quoteIdentifier($string);
        $this->assertEquals('`table_"_name`', $value);
    }

    /**
     * test that quoteIdentifer() accepts an integer
     * and returns a delimited identifier as with a string.
     */
    public function testAdapterQuoteIdentifierInteger()
    {
        $int = 123;
        $value = $this->_db->quoteIdentifier($int);
        $this->assertEquals('`123`', $value);
    }

    /**
     * test that quoteIdentifier() accepts a string
     * containing a dot (".") character, splits the
     * string, quotes each segment individually as
     * delimited identifers, and returns the imploded
     * string.
     */
    public function testAdapterQuoteIdentifierQualified()
    {
        $string = 'table.column';
        $value = $this->_db->quoteIdentifier($string);
        $this->assertEquals('`table`.`column`', $value);
    }

    /**
     * test that quoteIdentifer() escapes a single-quote
     * character in a string.
     */
    public function testAdapterQuoteIdentifierSingleQuote()
    {
        $string = "table_'_name";
        $value = $this->_db->quoteIdentifier($string);
        $this->assertEquals('`table_\'_name`', $value);
    }

    /**
     * test that quoteTableAs() accepts a string and an alias,
     * and returns each as delimited identifiers.
     * Most RDBMS want an 'AS' in between.
     */
    public function testAdapterQuoteTableAs()
    {
        $string = "foo";
        $alias = "bar";
        $value = $this->_db->quoteTableAs($string, $alias);
        $this->assertEquals('`foo` AS `bar`', $value);
    }

    /**
     * test that describeTable() returns correct types
     * @group ZF-3624
     *
     */
    public function testAdapterDescribeTableAttributeColumnFloat()
    {
        $desc = $this->_db->describeTable('zfprice');
        $this->assertEquals('zfprice',  $desc['price']['TABLE_NAME']);
        $this->assertRegExp('/float/i', $desc['price']['DATA_TYPE']);
    }

    /**
     * Ensures that the PDO Buffered Query does not throw the error
     * 2014 General error
     *
     * @link   http://framework.zend.com/issues/browse/ZF-2101
     * @return void
     */
    public function testZF2101()
    {
        $params = $this->_util->getParams();
        $db = Zend_Db::factory($this->getDriver(), $params);

        // Set default bound value
        $customerId = 1;

        // Stored procedure returns a single row
        $stmt = $db->prepare('CALL zf_test_procedure(?)');
        $stmt->bindParam(1, $customerId);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $this->assertEquals(1, $result[0]['product_id']);

        // Reset statement
        $stmt->closeCursor();

        // Stored procedure returns a single row
        $stmt = $db->prepare('CALL zf_test_procedure(?)');
        $stmt->bindParam(1, $customerId);
        $stmt->execute();
        $this->assertEquals(1, $result[0]['product_id']);
    }

    public function testAdapterAlternateStatement()
    {
        $this->_testAdapterAlternateStatement('Test_MysqliStatement');
    }

    public function getDriver()
    {
        return 'Mysqli';
    }

    /**
     * @group ZF-1541
     */
    public function testCharacterSetUtf8()
    {
        // Create a new adapter
        $params = $this->_util->getParams();

        $params['charset'] = 'utf8';

        $db = new Whitewashing_Db_Adapter_Mysql($params);

         // create a new util object, with the new db adapter
        $driver = $this->getDriver();
        $utilClass = "Zend_Db_TestUtil_{$driver}";
        $util = new $utilClass();
        $util->setAdapter($db);

        // create test table using no identifier quoting
        $util->createTable('charsetutf8', array(
            'id'    => 'IDENTITY',
            'stuff' => 'VARCHAR(32)'
        ));
        $tableName = $this->_util->getTableName('charsetutf8');

        // insert into the table
        $numRows = $db->insert($tableName, array(
            'id'    => 1,
            'stuff' => 'äöüß'
        ));

        // check if the row was inserted as expected
        $select = $db->select()->from($tableName, array('id', 'stuff'));

        $stmt = $db->query($select);
        $fetched = $stmt->fetchAll(Zend_Db::FETCH_NUM);
        $a = array(
            0 => array(0 => 1, 1 => 'äöüß')
        );
        $this->assertEquals($a, $fetched,
            'result of query not as expected');

        // clean up
        unset($stmt);
        $util->dropTable($tableName);
    }

    /**
     * Test AUTO_QUOTE_IDENTIFIERS option
     * Case: Zend_Db::AUTO_QUOTE_IDENTIFIERS = true
     */
    /*public function testAdapterAutoQuoteIdentifiersTrue()
    {
        $params = $this->_util->getParams();

        $params['options'] = array(
            Zend_Db::AUTO_QUOTE_IDENTIFIERS => true
        );
        $db = new Whitewashing_Db_Adapter_Mysql($params);
        $db->getConnection();

        $select = $this->_db->select();
        $select->from('zfproducts');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(3, count($result), 'Expected 3 rows in first query result');

        $this->assertEquals(1, $result[0]['product_id']);

        $select = $this->_db->select();
        $select->from('ZFPRODUCTS');
        try {
            $stmt = $this->_db->query($select);
            $result = $stmt->fetchAll();
            $this->fail('Expected exception not thrown');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
        }
    }*/

    /**
     * Test AUTO_QUOTE_IDENTIFIERS option
     * Case: Zend_Db::AUTO_QUOTE_IDENTIFIERS = false
     */
    public function testAdapterAutoQuoteIdentifiersFalse()
    {
        $params = $this->_util->getParams();

        $params['options'] = array(
            Zend_Db::AUTO_QUOTE_IDENTIFIERS => false
        );
        $db = new Whitewashing_Db_Adapter_Mysql($params);
        $db->getConnection();

        // create a new util object, with the new db adapter
        $driver = $this->getDriver();
        $utilClass = "Zend_Db_TestUtil_{$driver}";
        $util = new $utilClass();
        $util->setAdapter($db);

        // create test table using no identifier quoting
        $util->createTable('noquote', array(
            'id'    => 'INT NOT NULL PRIMARY KEY',
            'stuff' => 'CHAR(10)'
        ));
        $tableName = $this->_util->getTableName('noquote');

        // insert into the table
        $numRows = $db->insert($tableName, array(
            'id'    => 1,
            'stuff' => 'no quote 1'
        ));
        $this->assertEquals(1, $numRows,
            'number of rows in first insert not as expected');

        // check if the row was inserted as expected
        $sql = "SELECT id, stuff FROM $tableName ORDER BY id";
        $stmt = $db->query($sql);
        $fetched = $stmt->fetchAll(Zend_Db::FETCH_NUM);
        $a = array(
            0 => array(0 => 1, 1 => 'no quote 1')
        );
        $this->assertEquals($a, $fetched,
            'result of first query not as expected');

        // insert into the table using other case
        $numRows = $db->insert($tableName, array(
            'ID'    => 2,
            'STUFF' => 'no quote 2'
        ));
        $this->assertEquals(1, $numRows,
            'number of rows in second insert not as expected');

        // check if the row was inserted as expected
        $sql = 'SELECT ID, STUFF FROM ' . $tableName . ' ORDER BY ID';
        $stmt = $db->query($sql);
        $fetched = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $a = array(
            0 => array(0 => 1, 1 => 'no quote 1'),
            1 => array(0 => 2, 1 => 'no quote 2'),
        );
        $this->assertEquals($a, $fetched,
            'result of second query not as expected');

        // clean up
        unset($stmt);
        $util->dropTable($tableName);
    }
}
