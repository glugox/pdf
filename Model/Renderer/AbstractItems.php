<?php

/*
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Products Pdf Items renderer Abstract
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractItems extends \Magento\Framework\Model\AbstractModel {

    /**
     * Product model
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Products models
     *
     * @var \Magento\Catalog\Api\Data\ProductInterface[]
     */
    protected $_products;

    /**
     * Pdf object
     *
     * @var \Glugox\PDF\Model\Provider\PDF\AbstractPdf
     */
    protected $_pdf;

    /**
     * Pdf current page
     *
     * @var \Zend_Pdf_Page
     */
    protected $_pdfPage;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_string;

    /** @var \Glugox\PDF\Helper\Data */
    protected $_helper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /** @var \Magento\Catalog\Helper\Output $outputHelper */
    protected $_outputHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig
     */
    protected $_catalogProductMediaConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem ,
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Tax\Helper\Data $taxData,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\Framework\Filter\FilterManager $filterManager,
            \Magento\Framework\Stdlib\StringUtils $string,
            \Glugox\PDF\Helper\Data $helper,
            \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
            \Magento\Catalog\Helper\Output $outputHelper,
            \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
            \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
            \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
            array $data = []
    ) {
        $this->filterManager = $filterManager;
        $this->_taxData = $taxData;
        $this->_catalogProductMediaConfig = $catalogProductMediaConfig;
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_string = $string;
        $this->_helper = $helper;
        $this->_priceCurrency = $priceCurrency;
        $this->_outputHelper = $outputHelper;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    /**
     * Set products models
     *
     * @param  \Magento\Catalog\Api\Data\ProductInterface[] $products
     * @return $this
     */
    public function setProducts($products) {
        $this->_products = $products;
        return $this;
    }


    /**
     * Set product object
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Model\Product $product) {
        $this->_product = $product;
        return $this;
    }


    /**
     * Set Pdf model
     *
     * @param  \Glugox\PDF\Model\Provider\PDF\AbstractPdf $pdf
     * @return $this
     */
    public function setPdf(\Glugox\PDF\Model\Provider\PDF\AbstractPdf $pdf) {
        $this->_pdf = $pdf;
        return $this;
    }


    /**
     * Set current page
     *
     * @param  \Zend_Pdf_Page $page
     * @return $this
     */
    public function setPage(\Zend_Pdf_Page $page) {
        $this->_pdfPage = $page;
        return $this;
    }


    /**
     * Retrieve products array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProducts() {
        if (null === $this->_products) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The products are not specified.'));
        }
        return $this->_products;
    }


    /**
     * Retrieve product object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct() {
        if (null === $this->_product) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A product object is not specified.'));
        }
        return $this->_product;
    }


    /**
     * Retrieve Pdf model
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Glugox\PDF\Model\Provider\PDF\AbstractPdf
     */
    public function getPdf() {
        if (null === $this->_pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A PDF object is not specified.'));
        }
        return $this->_pdf;
    }


    /**
     * Retrieve Pdf page object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Zend_Pdf_Page
     */
    public function getPage() {
        if (null === $this->_pdfPage) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A PDF page object is not specified.'));
        }
        return $this->_pdfPage;
    }


    /**
     * Draw item line
     *
     * @return void
     */
    abstract public function draw();

    /**
     * Format option value process
     *
     * @param array|string $value
     * @return string
     */
    protected function _formatOptionValue($value) {
        return $value;
    }


    /**
     * Set font as regular
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    /* protected function _setFontRegular($size = 7) {
      $font = \Zend_Pdf_Font::fontWithPath(
      $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
      );
      $this->getPage()->setFont($font, $size);
      return $font;
      } */

    /**
     * Set font as regular
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($size = 7) {

        $fontSource = $this->_helper->getConfigObject()->getPdfFontRegular();
        return $this->_setFontBySource($fontSource, $size);
    }


    /**
     *
     * @param string $source
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBySource($fontSource, $size){
        $font = null;
        $bySource = 1;
        $path = "";
        try {
            $font = \Zend_Pdf_Font::fontWithName($fontSource);
            $bySource = 2;
        } catch (\Zend_Pdf_Exception $ex) {
            //
        }
        if (!$font) {
            try {
                $font = \Zend_Pdf_Font::fontWithPath($fontSource);
                $bySource = 3;
            } catch (\Zend_Pdf_Exception $ex) {
                //
            }
        }

        //die("S: " . $bySource . " : " . $fontSource);
        $this->getPage()->setFont($font, $size);
        //$fontName = (string)$font;
        /*$extracted = $this->getPage()->extractFonts();
        foreach ($extracted as $resourceId => $extractedFont){
            $test = ($extractedFont->getAscent() / $extractedFont->getUnitsPerEm() * $size);
            $this->_helper->info("- " . $resourceId . " >> " . $test);
        }

        $this->_helper->info("Font : " . $fontSource . " >> " . $fontName . " - " . \count($extracted));*/
        //
        return $font;
    }


    /**
     * Set font as bold
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    /* protected function _setFontBold($size = 7) {
      $font = \Zend_Pdf_Font::fontWithPath(
      $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
      );
      $this->getPage()->setFont($font, $size);
      return $font;
      } */

    /**
     * Set font as bold
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($size = 7) {
        $fontSource = $this->_helper->getConfigObject()->getPdfFontBold();
        return $this->_setFontBySource($fontSource, $size);
    }


    /**
     * Set font as italic
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    /* protected function _setFontItalic($size = 7) {
      $font = \Zend_Pdf_Font::fontWithPath(
      $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
      );
      $this->getPage()->setFont($font, $size);
      return $font;
      } */

    /**
     * Set font as italic
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($size = 7) {
        $fontSource = $this->_helper->getConfigObject()->getPdfFontItalic();
        return $this->_setFontBySource($fontSource, $size);
    }


    /**
     *
     * @param type $font
     * @param type $size
     * @return int
     */
    protected function getLineHeight($font, $fontSize){
        $k = 0.7;
        return $fontSize*$k;
    }

    /**
     *
     * @param type $font
     * @param type $size
     * @return int
     */
    protected function getFontDescent($font, $fontSize){

    }


}
