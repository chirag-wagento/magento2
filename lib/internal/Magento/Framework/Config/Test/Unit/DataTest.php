<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Config\Test\Unit;

class DataTest extends \PHPUnit_Framework_TestCase
{
   /**
     * @var \Magento\Framework\Config\ReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->readerMock = $this->getMock(\Magento\Framework\Config\ReaderInterface::class);
        $this->cacheMock = $this->getMock(\Magento\Framework\Config\CacheInterface::class);
        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->mockObjectManager([\Magento\Framework\Serialize\SerializerInterface::class => $this->serializerMock]);
    }

    protected function tearDown()
    {
        $reflectionProperty = new \ReflectionProperty(\Magento\Framework\App\ObjectManager::class, '_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);
    }

    /**
     * Mock application object manager to return configured dependencies.
     *
     * @param array $dependencies
     * @return void
     */
    private function mockObjectManager($dependencies)
    {
        $dependencyMap = [];
        foreach ($dependencies as $type => $instance) {
            $dependencyMap[] = [$type, $instance];
        }
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($dependencyMap));
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
    }

    public function testGetConfigNotCached()
    {
        $data = ['a' => 'b'];
        $cacheId = 'test';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->readerMock->expects($this->once())
            ->method('read')
            ->willReturn($data);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data);
        $config = new \Magento\Framework\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId
        );
        $this->assertEquals($data, $config->get());
        $this->assertEquals('b', $config->get('a'));
        $this->assertEquals(null, $config->get('a/b'));
        $this->assertEquals(33, $config->get('a/b', 33));
    }

    public function testGetConfigCached()
    {
        $data = ['a' => 'b'];
        $jsonString = '{"a":"b"}';
        $cacheId = 'test';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn($jsonString);
        $this->readerMock->expects($this->never())
            ->method('read');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($jsonString)
            ->willReturn($data);
        $config = new \Magento\Framework\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId
        );
        $this->assertEquals($data, $config->get());
        $this->assertEquals('b', $config->get('a'));
    }

    public function testReset()
    {
        $jsonString = '';
        $cacheId = 'test';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn($jsonString);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($jsonString)
            ->willReturn([]);
        $this->cacheMock->expects($this->once())
            ->method('remove')
            ->with($cacheId);
        $config = new \Magento\Framework\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId
        );
        $config->reset();
    }
}
