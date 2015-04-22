<?php

namespace Curse;

class SequentialCursorReaderTest extends \PHPUnit_Framework_TestCase
{
    private $reader = null;
    private $connection = null;

    protected function setUp()
    {
        $this->connection = $this->getMock('Curse\ConnectionInterface');
        $this->reader = new SequentialCursorReader($this->connection, 'sp_get_products', array());
    }

    protected function tearDown()
    {
        $this->connection = null;
        $this->reader = null;
    }

    public function testFetch()
    {
        $expectedResult = array(
            'product_id' => 1,
            'product_name' => 'test'
        );

        $databaseFetchResult = array(
            array(
                'product_id' => 1,
                'product_name' => 'test'
            )
        );

        $cursorName = 'cursor_1';

        $this->connection->expects($this->once())
            ->method('beginTransaction')
            ->will($this->returnValue(true));

        $this->connection->expects($this->at(1))
            ->method('executeQuery')
            ->with('SELECT sp_get_products() AS cursor_name', array())
            ->will($this->returnValue(array(
                array('cursor_name' => $cursorName)
            )));

        $this->connection->expects($this->at(2))
            ->method('executeQuery')
            ->with(sprintf('FETCH 1 FROM "%s"', $cursorName), array())
            ->will($this->returnValue($databaseFetchResult));

        $this->connection->expects($this->once())
            ->method('commit')
            ->will($this->returnValue(true));

        $this->reader->open();
        $result = $this->reader->fetch();
        $this->reader->close();

        $this->assertEquals($expectedResult, $result);
    }

    public function testIsOpenShouldReturnFalseWhenOpenMethodWasNotCalled()
    {
        $this->assertFalse($this->reader->isOpen());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You cannot fetch from a closed cursor
     */
    public function testFetchShouldThrowAnExceptionWhenReaderIsNotOpen()
    {
        $this->reader->fetch();
    }
}
