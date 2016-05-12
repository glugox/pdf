<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Page;

use Glugox\PDF\Exception\PDFException;
use Glugox\PDF\Model\Layout\Layout;
use Glugox\PDF\Model\Renderer\Data\Style;
use Glugox\PDF\Model\Renderer\Element;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\TestFramework\Inspection\Exception;


class Config extends \Magento\Framework\DataObject
{

    const PREFIX_MODULE = 'glugox_pdf/';

    /**
     * Section availability
     */
    const PRE_AVAIL = 'availability/';
    const ALLOWED_CUSTOMERS = self::PRE_AVAIL . 'allowed_customers';
    const ALLOWED_CUSTOMER_GROUPS = self::PRE_AVAIL . 'allowed_customer_groups';
    const ALLOWED_ON_ANCHOR_CATEGORIES = self::PRE_AVAIL . 'allowed_on_anchor_categories';
    const ALLOWED_ON_NON_ANCHOR_CATEGORIES = self::PRE_AVAIL . 'allowed_on_non_anchor_categories';
    const ALLOWED_ON_CATEGORY_PAGES = self::PRE_AVAIL . 'allowed_on_category_pages';
    const ALLOWED_ON_FRONTEND = self::PRE_AVAIL . 'allowed_on_frontend';
    const ALLOWED_ON_PRODUCT_PAGES = self::PRE_AVAIL . 'allowed_on_product_pages';

    /**
     * Section design
     */
    const PRE_D = 'design/';
    const BODY_PADDING = self::PRE_D . 'body_padding';
    const DISPLAY_ATTRIBUTES_IN_SINGLE_MODE = self::PRE_D . 'display_attributes_in_single_mode';
    const DISPLAY_CATEGORIES = self::PRE_D . 'display_categories';
    const DISPLAY_DESCRIPTION_IN_SINGLE_MODE = self::PRE_D . 'display_description_in_single_mode';
    const DISPLAY_LOGO = self::PRE_D . 'display_logo';
    const DISPLAY_PRICE = self::PRE_D . 'display_price';
    const DISPLAY_SKU = self::PRE_D . 'display_sku';
    const LIST_TITLE_MAX_LINES = self::PRE_D . 'list_title_max_lines';
    const DISPLAY_STORE_NAME = self::PRE_D . 'display_store_name';
    const HEADER_ON_EACH_PAGE = self::PRE_D . 'header_on_each_page';
    const LOGO_HEIGHT = self::PRE_D . 'logo_height';
    const LOGO_SRC = self::PRE_D . 'logo_src';
    const PAGE_WRAPPER_MARGIN = self::PRE_D . 'page_wrapper_margin';
    const STORE_NAME = self::PRE_D . 'store_name';
    const DISPLAY_CATEGORY_TITLE = self::PRE_D . 'display_category_title';
    const DISPLAY_CATEGORY_DESCRIPTION = self::PRE_D . 'display_category_description';

    /**
     * Section general
     */
    const PRE_GEN = 'general/';
    const ENABLED = self::PRE_GEN . 'enabled';
    const MAX_ITEMS_ON_LIST = self::PRE_GEN . 'max_items_on_list';
    const DEBUG_MODE = self::PRE_GEN . 'debug_mode';
    const CACHE_ENABLED = self::PRE_GEN . 'cache_enabled';

    /**
     * Section image
     */
    const PRE_IMG = 'image/';
    const LIST_IMAGE_MAX_HEIGHT = self::PRE_IMG . 'list_image_max_height';
    const LIST_IMAGE_MAX_WIDTH = self::PRE_IMG . 'list_image_max_width';
    const SHOW_IMAGE_IN_LIST_MODE = self::PRE_IMG . 'show_image_in_list_mode';
    const SHOW_IMAGE_IN_SINGLE_MODE = self::PRE_IMG . 'show_image_in_single_mode';
    const SINGLE_IMAGE_MAX_HEIGHT = self::PRE_IMG . 'single_image_max_height';
    const SINGLE_IMAGE_MAX_WIDTH = self::PRE_IMG . 'single_image_max_width';

    /**
     * Section typography
     */
    const PRE_TYPO = 'typography/';
    const COLOR_CATEGORIES = self::PRE_TYPO . 'color_categories';
    const COLOR_LINES = self::PRE_TYPO . 'color_lines';
    const COLOR_PRICE = self::PRE_TYPO . 'color_price';
    const COLOR_PRICE_OLD = self::PRE_TYPO . 'color_price_old';
    const COLOR_SKU = self::PRE_TYPO . 'color_sku';
    const COLOR_STORE_NAME = self::PRE_TYPO . 'color_store_name';
    const COLOR_TEXT = self::PRE_TYPO . 'color_text';
    const COLOR_TITLE = self::PRE_TYPO . 'color_title';
    const FONT_BOLD = self::PRE_TYPO . 'font_bold';
    const FONT_REGULAR = self::PRE_TYPO . 'font_regular';
    const LIST_TITLE_SIZE = self::PRE_TYPO . 'list_title_size';
    const SINGE_TITLE_SIZE = self::PRE_TYPO . 'single_title_size';


    /**
     * Events
     */
    const EVENT_ELEMENT_RENDER_START = 'glugox_pdf_element_render_start';
    const EVENT_ELEMENT_RENDER_END = 'glugox_pdf_element_render_end';

    /**
     * Default
     */
    const BLOCK_DEFAULT_HEIGHT = 0;
    const CONTAINER_DEFAULT_HEIGHT = 0;


    /**
     * Pdf types
     */
    const PDF_TYPE_PRODUCT = 'pdf_type_product';
    const PDF_TYPE_LIST = 'pdf_type_list';

    /**
     * Config data loader
     *
     * @var \Magento\Config\Model\Config\Loader
     */
    protected $_configLoader;

    /**
     * @var Title
     */
    protected $title;

    /**
     * @var \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    protected $_boundingBox = null;

    /**
     * @var \Glugox\PDF\Model\Renderer\Data\BoundingBoxFactory
     */
    protected $_boundingBoxFactory = null;

    /**
     * @var string Page size, default: "595:842:"
     */
    protected $_pageSize = \Zend_Pdf_Page::SIZE_A4;

    /**
     * @var \Glugox\PDF\Model\Renderer\RendererInterface
     */
    protected $_currentRenderingElement = null;


    /**
     * @var array
     */
    protected $_renderedElements = [];


    /**
     * @var \Glugox\PDF\Model\Layout\LayoutInterface
     */
    protected $layout;

    /**
     * @var string
     */
    protected $pageLayout;


    /**
     * If we are rendering only one product -> product,
     * If we are rendering multiple products -> list
     *
     * @var string
     */
    protected $_pdfType;

    /**
     * @return string
     */
    public function getPdfType()
    {
        return $this->_pdfType;
    }


    /**
     * Is the config data loaded from database
     *
     * @var bool
     */
    protected $_isLoaded = false;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;


    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManeger = null;


    /** @var  \Glugox\PDF\Helper\Data */
    protected $_helper;


    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product = null;


    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollection = null;


    /**
     * @var string
     */
    protected $_pdfTitle;


    /**
     * @var string
     */
    protected $_pdfDescription;

    /**
     * Config constructor.
     * @param \Glugox\PDF\Model\Layout\LayoutInterface $layout
     * @param State $state
     */
    public function __construct(
        \Glugox\PDF\Model\Layout\LayoutInterface $layout,
        \Magento\Config\Model\Config\Loader $configLoader,
        \Glugox\PDF\Model\Renderer\Data\BoundingBoxFactory $boundingBoxFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Glugox\PDF\Helper\Data $helper
    )
    {
        $this->layout = $layout;
        $this->_configLoader = $configLoader;
        $this->_boundingBoxFactory = $boundingBoxFactory;
        $this->_eventManager = $eventManager;
        $this->_objectManeger = $objectManager;
        $this->_helper = $helper;

        $this->layout->setConfig($this);
        $this->load();
    }


    /**
     * Loads config data from database
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function load()
    {

        if (!$this->_isLoaded) {
            $this->_isLoaded = true;

            $data = $this->_configLoader->getConfigByPath(\trim(self::PREFIX_MODULE, '/'), 'default', 0, true);
            foreach ($data as $cfgKey => $item) {
                $cfgKey = \str_replace(self::PREFIX_MODULE, '', $cfgKey);
                $this->setData($cfgKey, $item["value"]);
            }
        }
        return $this;

    }


    /**
     * Initializes the zend pdf instance and
     * prepares it for rendering.
     *
     */
    public function render()
    {

        $rootRenderer = $this->getLayout()->getRootRenderer();
        $rootRenderer->initialize($this);
        $rootRenderer->boot();

        $rendered = $rootRenderer->render();
        while (Element::NEW_PAGE_FLAG === $rendered) {
            $rendered = $this->renderOnNewPage();
        }

        return $rendered;
    }


    /**
     * @param $rootRenderer
     * @param $pageSelector
     * @param $configPath
     * @param $styleKey
     * @param bool $isBool
     * @param isInt $
     */
    protected function processConfigStyle($rootRenderer, $pageSelector, $configPath, $styleKey, $isBool = false, $isInt = false)
    {
        $value = $this->getData($configPath);
        if (empty($value)) {
            return;
        }
        if ($isBool) {
            $value = (boolean)$value;
        } else if ($isInt) {
            $value = (int)$value;
        }
        $renderer = $rootRenderer->getChild($pageSelector);
        if ($renderer) {
            if (Style::STYLE_DISPLAY === $styleKey) {
                $value = ($value ? "block" : "none");
            }
            $renderer->getStyle()->set($styleKey, $value);
        }
    }


    /**
     * Element styles can be set in the xml files, but if the user
     * has set some styling in the admin config , than we will override
     * the styles with the config values.
     */
    public function processConfigStyling()
    {


        $rootRenderer = $this->getLayout()->getRootRenderer();
        $pCont = "wrapper/product-page/product-container";
        $data = [
            // page
            ["wrapper/product-page", self::BODY_PADDING, Style::STYLE_PADDING, false, true],
            ["wrapper/product-page", self::COLOR_TEXT, Style::STYLE_COLOR, false, false],

            // header
            ["wrapper/product-page/header-wrapper/logo", self::LOGO_HEIGHT, Style::STYLE_HEIGHT, false, true],
            ["wrapper/product-page/header-wrapper/logo", self::DISPLAY_LOGO, Style::STYLE_DISPLAY, true, false],
            ["wrapper/product-page/header-wrapper/store-name", self::LOGO_HEIGHT, Style::STYLE_HEIGHT, false, true],
            ["wrapper/product-page/header-wrapper/store-name", self::DISPLAY_STORE_NAME, Style::STYLE_DISPLAY, true, false],
            ["wrapper/product-page/header-wrapper/header-line", self::COLOR_LINES, Style::STYLE_COLOR, false, false],
            ["wrapper/product-page/header-wrapper/store-name", self::COLOR_STORE_NAME, Style::STYLE_COLOR, false, false],

            // content

            [$pCont . "/price-container/product-price", self::DISPLAY_PRICE, Style::STYLE_DISPLAY, true, false],
            [$pCont . "/repeater-item/price", self::DISPLAY_PRICE, Style::STYLE_DISPLAY, true, false],
            [$pCont . "/repeater-item/price", self::COLOR_PRICE, Style::STYLE_COLOR, false, false],
            [$pCont . "/repeater-item/title", self::COLOR_TITLE, Style::STYLE_COLOR, false, false],
            [$pCont . "/repeater-item/title", self::LIST_TITLE_MAX_LINES, Style::STYLE_MAX_LINES, false, true],
            [$pCont . "/title-container/product-categories", self::DISPLAY_CATEGORIES, Style::STYLE_DISPLAY, true, false],
            [$pCont . "/title-container/product-sku", self::DISPLAY_SKU, Style::STYLE_DISPLAY, true, false],
            [$pCont . "/desc-container/description", self::DISPLAY_DESCRIPTION_IN_SINGLE_MODE, Style::STYLE_DISPLAY, true, false],
            [$pCont . "/attr-container/attributes", self::DISPLAY_ATTRIBUTES_IN_SINGLE_MODE, Style::STYLE_DISPLAY, true, false],
            [$pCont . "/title-container/product-title", self::SINGE_TITLE_SIZE, Style::STYLE_FONT_SIZE, false, true],
            [$pCont . "/title-container/product-categories", self::COLOR_CATEGORIES, Style::STYLE_COLOR, false, false],
            [$pCont . "/title-container/product-sku", self::COLOR_SKU, Style::STYLE_COLOR, false, false],
            [$pCont . "/price-container/product-price", self::COLOR_PRICE, Style::STYLE_COLOR, false, false],
            [$pCont . "/price-container/product-price", self::COLOR_PRICE_OLD, Style::STYLE_COLOR_PRICE_OLD, false, false],
            [$pCont . "/title-container/product-title", self::COLOR_TITLE, Style::STYLE_COLOR, false, false],

        ];

        foreach ($data as $item) {
            $this->processConfigStyle($rootRenderer, $item[0], $item[1], $item[2], $item[3], $item[4]);
        }


        // Title font size in list mode
        $titleFontSizeLM = (int)$this->getData(self::LIST_TITLE_SIZE);

        // Font for the regular texts
        $font = $this->getData(self::FONT_REGULAR);

        // Font for the bold texts
        $fontBold = $this->getData(self::FONT_BOLD);

        // Lines color
        $linesColor = $this->getData(self::COLOR_LINES);

        // Categories color
        $categoriesColor = $this->getData(self::COLOR_CATEGORIES);

        // SKU color
        $skuColor = $this->getData(self::COLOR_SKU);

        // Store name color
        $storeNameColor = $this->getData(self::COLOR_STORE_NAME);

        // Price color
        $priceColor = $this->getData(self::COLOR_PRICE);

        // Discounted price color
        $discountedPriceColor = $this->getData(self::COLOR_PRICE_OLD);

        // Title color
        $titleColor = $this->getData(self::COLOR_TITLE);

        // Text color
        $textColor = $this->getData(self::COLOR_TEXT);

        // Show image in single mode
        $showImageSM = (boolean)$this->getData(self::SHOW_IMAGE_IN_SINGLE_MODE);

        // Maximum width of image in single mode
        $imageWidthSM = (int)$this->getData(self::SINGLE_IMAGE_MAX_WIDTH);

        // Maximum height of image in single mode
        $imageHeightSM = (int)$this->getData(self::SINGLE_IMAGE_MAX_HEIGHT);

        // Show image in list mode
        $showImageLM = (boolean)$this->getData(self::SHOW_IMAGE_IN_LIST_MODE);

        // Maximum width of image in list mode
        $imageWidthLM = (int)$this->getData(self::LIST_IMAGE_MAX_WIDTH);

        // Maximum height of image in list mode
        $imageHeightLM = (int)$this->getData(self::LIST_IMAGE_MAX_HEIGHT);

    }


    /**
     * Object data getter
     *
     * Disable parent's processing a/b/c key as ['a']['b']['c']
     *
     * @param string $key
     * @return mixed
     */
    public function getData($key = '', $default = null)
    {
        return $this->hasData($key) ? parent::_getData($key) : $default;
    }


    /**
     * @param $event
     * @param array $data
     */
    public function dispachEvent($event, $data = [])
    {
        $element = $data["element"];
        $elName = $element->getName();
        switch ($event) {
            case self::EVENT_ELEMENT_RENDER_START:
                $this->setCurrentRenderingElement($element);
                $this->getLayout()->getRootRenderer()->updateLayout();
                break;
            case self::EVENT_ELEMENT_RENDER_END:
                $this->addRenderedElement($element);
                //$this->getLayout()->getRootRenderer()->updateLayout();
                break;
            default:
                //
        }
    }


    /**
     * @return \Glugox\PDF\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }


    /**
     * @return string
     */
    public function getStoreName()
    {
        return $this->getHelper()->getStoreName();
    }


    /**
     * @param string $title
     */
    public function setPdfTitle($title)
    {
        $this->_pdfTitle = $title;
    }

    /**
     * @return string
     */
    public function getPdfTitle()
    {
        if(!$this->getData(self::DISPLAY_CATEGORY_TITLE)){
            return '';
        }
        return $this->_pdfTitle;
    }

    /**
     * @return string
     */
    public function getPdfDescription()
    {
        if(!$this->getData(self::DISPLAY_CATEGORY_DESCRIPTION)){
            return '';
        }
        return $this->_pdfDescription;
    }

    /**
     * @param string $pdfDescription
     */
    public function setPdfDescription($pdfDescription)
    {
        $this->_pdfDescription = $pdfDescription;
    }




    /**
     * Registers current/last rendered element to the config,
     * so we know the next relativly available position, and
     * current page state relative to that element.
     */
    public function setCurrentRenderingElement(\Glugox\PDF\Model\Renderer\RendererInterface $element)
    {
        $this->_currentRenderingElement = $element;
    }

    /**
     * Returns current/last rendering element
     *
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getCurrentRenderingElement()
    {
        return $this->_currentRenderingElement;
    }


    /**
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $element
     */
    public function addRenderedElement(\Glugox\PDF\Model\Renderer\RendererInterface $element)
    {
        $this->_renderedElements[] = $element->getName();
    }


    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        $rootRenderer = $this->getLayout()->getRootRenderer();
        $pdf = $rootRenderer->getPdf();

        $page = $pdf->newPage($this->getPageSize());
        $rootRenderer->getStyle()->applyToPage($page);

        $pdf->pages[] = $page;

        $rootRenderer->setPdfPage($page);
        $rootRenderer->handleNewPage();

        return $page;
    }


    /**
     * @return \Zend_Pdf
     */
    public function renderOnNewPage()
    {

        $this->newPage();
        if (true) {
            $header = $this->getLayout()->getRootRenderer()->getChild("wrapper/product-page/header-wrapper");
            if ($header) { // first time is null
                $header->setIsRendered(false, true);
            }

        }
        return $this->getLayout()->getRootRenderer()->render();
    }

    /**
     * Build page config
     * @return void
     */
    protected function build()
    {
        $this->layout->build();
    }


    /**
     * Retrieve title element text (encoded)
     *
     * @return Title
     */
    public function getTitle()
    {
        $this->build();
        return $this->title;
    }


    /**
     * @return \Glugox\PDF\Model\Layout\LayoutInterface
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set page layout
     *
     * @param string $handle
     * @return $this
     */
    public function setPageLayout($handle)
    {
        $this->pageLayout = $handle;
        return $this;
    }

    /**
     * Return current page layout
     *
     * @return string
     */
    public function getPageLayout()
    {
        return $this->pageLayout;
    }

    /**
     * @param string $value
     */
    public function setPageSize($value)
    {
        $this->_pageSize = $value;

    }

    /**
     * @return string
     */
    public function getPageSize()
    {
        return $this->_pageSize;
    }


    /**
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $element
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function createNewBoundingBoxFor(\Glugox\PDF\Model\Renderer\RendererInterface $element)
    {

        $style = $element->getStyle();
        $name = $element->getName();
        $parentBB = $element->hasParent() ? $element->getParent()->getBoundingBox() : $element->getConfig()->getBoundingBox();

        /**
         * Define relative position in the layout,
         * calculate absolute position of the element, using its
         * parent.
         */
        $newMaxWidth = $parentBB->getInnerWidth();
        //$newMaxHeight = $parentBB->getInnerHeight();

        $relX = 0; // relative position
        $relY = 0; // relative position

        // block type based values
        switch ($element->getType()) {
            case Layout::TYPE_BLOCK:
                // occupy some height just to be visible
                $newDefaultHeight = self::BLOCK_DEFAULT_HEIGHT;
                break;
            case Layout::TYPE_CONTAINER:
                $newDefaultHeight = self::CONTAINER_DEFAULT_HEIGHT;
                break;
            default:
                $newDefaultHeight = 1;
        }
        $newDefaultWidth = $newMaxWidth;

        $x = $style->get(Style::STYLE_LEFT, 0) + $relX;
        $y = $style->get(Style::STYLE_TOP, 0) + $relY;


        $width = $style->get(Style::STYLE_WIDTH, $newDefaultWidth);
        $height = $style->get(Style::STYLE_HEIGHT, $newDefaultHeight);

        /*if($width > $newMaxWidth){
            $width = $newMaxWidth;
        }
        if($height > $newMaxHeight){
            $height = $newMaxHeight;
        }*/

        return $this->createNewBoundingBox($x, $y, $width, $height, $element);
    }


    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function createPageBoundingBox()
    {

        $boundingBox = null;
        $sizeArr = \explode(":", $this->_pageSize);
        if (\count($sizeArr) >= 2) {
            $boundingBox = $this->createNewBoundingBox(0, 0, (int)$sizeArr[0], (int)$sizeArr[1]);
        } else {
            throw new PDFException(__("Invalid page size definition : '%1'", $this->_pageSize));
        }

        $boundingBox->setCanIncreaseHeight(false);
        $boundingBox->setCanIncreaseWidth(false);

        return $boundingBox;
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function createNewBoundingBox(
        $x,
        $y,
        $width,
        $height,
        \Glugox\PDF\Model\Renderer\RendererInterface $element = null
    )
    {
        return $this->_boundingBoxFactory
            ->create([
                'x' => $x,
                'y' => $y,
                'width' => $width,
                'height' => $height,
                'element' => $element
            ]);
    }

    /**
     * @var \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function getBoundingBox()
    {

        if (null == $this->_boundingBox) {
            $this->_boundingBox = $this->createPageBoundingBox();

        }
        return $this->_boundingBox;
    }


    /**
     * @param string $styleSource
     * @return \Glugox\PDF\Model\Renderer\Data\Style
     */
    public function createStyle($styleSource, \Glugox\PDF\Model\Renderer\RendererInterface $element)
    {
        $style = $this->_objectManeger->create('Glugox\PDF\Model\Renderer\Data\Style', ['source' => $styleSource, 'element' => $element]);
        return $style;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * @param Product $product
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function setProduct(Product $product)
    {
        $this->_product = $product;
        if (!empty($this->_pdfType)) {
            throw new PDFException(__("PDF type is already set to '%1'!", $this->_pdfType));
        }
        $this->_pdfType = self::PDF_TYPE_PRODUCT;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getProductCollection()
    {
        return $this->_productCollection;
    }

    /**
     * @param $productCollection
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function setProductCollection($productCollection)
    {
        $this->_productCollection = $productCollection;
        if (!empty($this->_pdfType)) {
            throw new PDFException(__("PDF type is already set to '%1'!", $this->_pdfType));
        }
        $this->_pdfType = self::PDF_TYPE_LIST;
        return $this;
    }


    /**
     * @return array
     */
    public function getProductItems()
    {
        $collection = $this->getProductCollection();
        if ($collection instanceof Collection) {
            $collection = $this->getProductCollection()->load();
        }
        return $collection;
    }


}