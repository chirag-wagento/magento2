<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Export;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Export\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializerMock;

    /**
     * @var string
     */
    protected $cacheId = 'some_id';

    /**
     * @var \Magento\ImportExport\Model\Export\Config
     */
    protected $model;

    protected function setUp()
    {
        $this->readerMock = $this->getMock(
            \Magento\ImportExport\Model\Export\Config\Reader::class,
            [],
            [],
            '',
            false
        );
        $this->cacheMock = $this->getMock(\Magento\Framework\Config\CacheInterface::class);
        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->mockObjectManager(
            [\Magento\Framework\Serialize\SerializerInterface::class => $this->serializerMock]
        );
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

    /**
     * @param array $value
     * @param null|string $expected
     * @dataProvider getEntitiesDataProvider
     */
    public function testGetEntities($value, $expected)
    {
        $this->cacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->cacheId
        )->will(
            $this->returnValue(false)
        );
        $this->readerMock->expects($this->any())->method('read')->will($this->returnValue($value));
        $this->model = new \Magento\ImportExport\Model\Export\Config(
            $this->readerMock,
            $this->cacheMock,
            $this->cacheId
        );
        $this->assertEquals($expected, $this->model->getEntities('entities'));
    }

    public function getEntitiesDataProvider()
    {
        return [
            'entities_key_exist' => [['entities' => 'value'], 'value'],
            'return_default_value' => [['key_one' => 'value'], null]
        ];
    }

    /**
     * @param array $configData
     * @param string $entity
     * @param string[] $expectedResult
     * @dataProvider getEntityTypesDataProvider
     */
    public function testGetEntityTypes($configData, $entity, $expectedResult)
    {
        $this->cacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->cacheId
        )->will(
            $this->returnValue(false)
        );
        $this->readerMock->expects($this->any())->method('read')->will($this->returnValue($configData));
        $this->model = new \Magento\ImportExport\Model\Export\Config(
            $this->readerMock,
            $this->cacheMock,
            $this->cacheId
        );
        $this->assertEquals($expectedResult, $this->model->getEntityTypes($entity));
    }

    public function getEntityTypesDataProvider()
    {
        return [
            'valid type' => [
                [
                    'entities' => [
                        'catalog_product' => [
                            'types' => ['configurable', 'simple'],
                        ],
                    ],
                ],
                'catalog_product',
                ['configurable', 'simple'],
            ],
            'not existing entity' => [
                [
                    'entities' => [
                        'catalog_product' => [
                            'types' => ['configurable', 'simple'],
                        ],
                    ],
                ],
                'not existing entity',
                [],
            ],
        ];
    }

    /**
     * @param array $value
     * @param null|string $expected
     * @dataProvider getFileFormatsDataProvider
     */
    public function testGetFileFormats($value, $expected)
    {
        $this->cacheMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->cacheId
        )->will(
            $this->returnValue(false)
        );
        $this->readerMock->expects($this->any())->method('read')->will($this->returnValue($value));
        $this->model = new \Magento\ImportExport\Model\Export\Config(
            $this->readerMock,
            $this->cacheMock,
            $this->cacheId
        );
        $this->assertEquals($expected, $this->model->getFileFormats('fileFormats'));
    }

    public function getFileFormatsDataProvider()
    {
        return [
            'fileFormats_key_exist' => [['fileFormats' => 'value'], 'value'],
            'return_default_value' => [['key_one' => 'value'], null]
        ];
    }
}
