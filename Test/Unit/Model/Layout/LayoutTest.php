<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Test\Unit\Model\Layout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class LayoutTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ObjectManagerHelper
     */
    protected $_objectManagerHelper;


    /**
     * @var \Glugox\PDF\Model\Layout\Layout
     */
    protected $_layout;

    /**
     * @var \Glugox\PDF\Model\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \Glugox\PDF\Model\Renderer\RootRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_rootRendererMock;

    /**
     * @var \Glugox\PDF\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pdfHelperMock;

    /**
     * Set up
     */
    protected function setUp()
    {

        $this->_objectManagerHelper = new ObjectManagerHelper($this);

        /**
         * Helper
         */
        $this->_createHelperMock();


        $this->_rootRendererMock = $this->getMockBuilder("Glugox\PDF\Model\Renderer\RootRenderer")->disableOriginalConstructor()->getMock();

        /**
         * Layout
         */
        $this->_layout = $this->_objectManagerHelper->getObject('Glugox\PDF\Model\Layout\Layout', [
            "rootRenderer" => $this->_rootRendererMock
        ]);


        /**
         * Config
         */
        $this->_configMock = $this->_objectManagerHelper->getObject('Glugox\PDF\Model\Page\Config', [
            "layout" => $this->_layout,
            "helper" => $this->_pdfHelperMock
        ]);
        $this->_layout->setConfig($this->_configMock);


    }

    /**
     * Creates helper mock
     */
    protected function  _createHelperMock(){

        $pdfResult = $this->_objectManagerHelper->getObject("Glugox\PDF\Model\PDFResult");
        $this->_pdfHelperMock = $this->getMockBuilder("Glugox\PDF\Helper\Data")->disableOriginalConstructor()->getMock();
        $this->_pdfHelperMock->expects($this->any())->method("getInstance")->will($this->returnCallback([$this,"getInstance"]));
        $this->_pdfHelperMock->expects($this->any())->method("createPdfResult")->will($this->returnValue($pdfResult));
        $this->_pdfHelperMock->expects($this->any())
            ->method("info")
            ->will($this->returnCallback([$this,"logMessage"]));
    }

    /**
     * Log message
     */
    public function logMessage( $a, $b = false ){
        //var_dump($a);
    }

    /**
     * Get instance
     */
    public function getInstance( $class ){
        return $this->_objectManagerHelper->getObject($class);
    }



    /**
     * Test layout can load
     */
    public function testLoad(){

        $this->_layout->load();
        $xml = $this->_layout->getXmlString();
        $this->assertXmlStringEqualsXmlString('<layouts/>', $xml);
    }
}