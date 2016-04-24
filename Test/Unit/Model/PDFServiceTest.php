<?php

/*
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Glugox\PDF\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Description of PDFServiceTest
 *
 * @author Eko
 */
class PDFServiceTest extends \PHPUnit_Framework_TestCase{

    /**
     * @var ObjectManagerHelper
     */
    protected $_objectManagerHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Glugox\PDF\Model\PDFService
     */
    protected $_pdfService;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productMock;

    /**
     * @var \Glugox\PDF\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pdfHelperMock;

    /**
     *
     * @var \Glugox\PDF\Model\Provider\PDF\ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pdfProviderMock;

    /**
     * @var \Glugox\PDF\Model\Provider\Products\ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pdfProductsProviderMock;



    /**
     * Set up
     */
    protected function setUp()
    {


        $this->_objectManagerHelper = new ObjectManagerHelper($this);

        /**
         * Test product to work with
         */
        $this->_productMock = $this->_objectManagerHelper->getObject("Magento\Catalog\Model\Product");
        $this->_productMock->setData([
            "name" => "Test Product",
            "sku" => "A1",
            "description" => "Hello, this is a test product.",
            "price" => 120
        ]);

        /**
         * Product provider will return our test product to print
         */
        $this->_pdfProductsProviderMock = $this->getMockBuilder("Glugox\PDF\Model\Provider\Products\ProviderInterface")->getMock();

        /**
         * Helper will return products provider mock above to get out test product mock
         */
        $pdfResult = $this->_objectManagerHelper->getObject("Glugox\PDF\Model\PDFResult");;

        $this->_pdfHelperMock = $this->getMockBuilder("Glugox\PDF\Helper\Data")->disableOriginalConstructor()->getMock();
        $this->_pdfHelperMock->expects($this->any())->method("getProductsProvider")->will($this->returnValue($this->_pdfProductsProviderMock));
        $this->_pdfHelperMock->expects($this->any())->method("getInstance")->will($this->returnCallback([$this,"getInstance"]));
        $this->_pdfHelperMock->expects($this->any())->method("createPdfResult")->will($this->returnValue($pdfResult));


        //
        $this->_pdfHelperMock->expects($this->any())
                ->method("info")
                ->will($this->returnCallback([$this,"logMessage"]));

        /**
         * PDF Provider actualy creates the pdf
         */
        $this->_pdfProvider = $this->_objectManagerHelper->getObject("Glugox\PDF\Model\Provider\PDF", [
            'helper' => $this->_pdfHelperMock,
            'string' => $this->_objectManagerHelper->getObject("Magento\Framework\Stdlib\StringUtils"),
            'filesystem' => $this->_objectManagerHelper->getObject("Magento\Framework\Filesystem"),
            'localeDate' => $this->_objectManagerHelper->getObject("Magento\Framework\Stdlib\DateTime\Timezone"),
        ]);

        $this->_pdfHelperMock->expects($this->any())->method("getPDFProvider")->will($this->returnValue($this->_pdfProvider));


        $this->_pdfService = $this->_objectManagerHelper->getObject('Glugox\PDF\Model\PDFService', [
            "pdfFactory" => $this->_objectManagerHelper->getObject("Glugox\PDF\Model\PDFFactory"),
            "helper" => $this->_pdfHelperMock,
            "pdfProvider" => $this->_pdfProvider
        ]);

    }


    /**
     * Log message
     */
    public function logMessage( $a, $b = false ){
        //var_dump($a);
    }

    /**
     * Log message
     */
    public function getInstance( $class ){
        return $this->_objectManagerHelper->getObject($class);
    }



    /**
     * Test category processing sku option (SKU1, SKU2, etc)
     */
    public function testCreateProductPDF(){

        // \Glugox\PDF\Model\PDFResult
        $pdfResult = $this->_pdfService->serve("SKU-X999");
        $error = $pdfResult->getError();
        $expectedError = "Product SKU-X999 not found!";
        $errors = \explode("; ", $error);

        $this->assertNotNull($pdfResult);
        $this->assertContains($expectedError, $errors);
    }

    /**
     * Test category processing multiple skus option ('SKU1,SKU2', etc)
     */
    public function testCreateProductsPDF(){

        // \Glugox\PDF\Model\PDFResult
        $pdfResult = $this->_pdfService->serve("SKU-X999,SKU-Y999");
        $error = $pdfResult->getError();
        $expectedError = "Products SKU-X999,SKU-Y999 not found!";
        $errors = \explode("; ", $error);

        $this->assertNotNull($pdfResult);
        $this->assertContains($expectedError, $errors);
    }

    /**
     * Test category processing category option (-c12, -c3, etc)
     */
    public function testCreateCategoryPDF(){

        // \Glugox\PDF\Model\PDFResult
        $pdfResult = $this->_pdfService->serve("-c999");
        $error = $pdfResult->getError();
        $expectedError = "Products for category 999 not found!";
        $errors = \explode("; ", $error);

        $this->assertNotNull($pdfResult);
        $this->assertContains($expectedError, $errors);
    }


}
