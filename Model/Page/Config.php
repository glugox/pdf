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


class Config extends \Magento\Framework\DataObject
{

    const PREFIX_MODULE = 'glugox_pdf/';

    /**
     * Section availability
     */
    const PRE_AVAIL                            = 'availability/';
    const ALLOWED_CUSTOMERS                    = self::PRE_AVAIL . 'allowed_customers';
    const ALLOWED_CUSTOMER_GROUPS              = self::PRE_AVAIL . 'allowed_customer_groups';
    const ALLOWED_ON_ANCHOR_CATEGORIES         = self::PRE_AVAIL . 'allowed_on_anchor_categories';
    const ALLOWED_ON_NON_ANCHOR_CATEGORIES     = self::PRE_AVAIL . 'allowed_on_non_anchor_categories';
    const ALLOWED_ON_CATEGORY_PAGES            = self::PRE_AVAIL . 'allowed_on_category_pages';
    const ALLOWED_ON_FRONTEND                  = self::PRE_AVAIL . 'allowed_on_frontend';
    const ALLOWED_ON_PRODUCT_PAGES             = self::PRE_AVAIL . 'allowed_on_product_pages';

    /**
     * Section design
     */
    const PRE_D                                = 'design/';
    const BODY_PADDING                         = self::PRE_D . 'body_padding';
    const DISPLAY_ATTRIBUTES_IN_SINGLE_MODE    = self::PRE_D . 'display_attributes_in_single_mode';
    const DISPLAY_CATEGORIES                   = self::PRE_D . 'display_categories';
    const DISPLAY_DESCRIPTION_IN_SINGLE_MODE   = self::PRE_D . 'display_description_in_single_mode';
    const DISPLAY_LOGO                         = self::PRE_D . 'display_logo';
    const DISPLAY_PRICE                        = self::PRE_D . 'display_price';
    const DISPLAY_SKU                          = self::PRE_D . 'display_sku';
    const DISPLAY_STORE_NAME                   = self::PRE_D . 'display_store_name';
    const GENERAL_PADDING                      = self::PRE_D . 'general_padding';
    const HEADER_ON_EACH_PAGE                  = self::PRE_D . 'header_on_each_page';
    const LOGO_HEIGHT                          = self::PRE_D . 'logo_height';
    const LOGO_SRC                             = self::PRE_D . 'logo_src';
    const PAGE_WRAPPER_MARGIN                  = self::PRE_D . 'page_wrapper_margin';
    const STORE_NAME                           = self::PRE_D . 'store_name';

    /**
     * Section general
     */
    const PRE_GEN                              = 'general/';
    const ENABLED                              = self::PRE_GEN . 'enabled';
    const MAX_ITEMS_ON_LIST                    = self::PRE_GEN . 'max_items_on_list';
    const DEBUG_MODE                           = self::PRE_GEN . 'debug_mode';

    /**
     * Section image
     */
    const PRE_IMG                              = 'image/';
    const LIST_IMAGE_MAX_HEIGHT                = self::PRE_IMG . 'list_image_max_height';
    const LIST_IMAGE_MAX_WIDTH                 = self::PRE_IMG . 'list_image_max_width';
    const SHIW_IMAGE_IN_LIST_MODE              = self::PRE_IMG . 'show_image_in_list_mode';
    const SHIW_IMAGE_IN_SINGLE_MODE            = self::PRE_IMG . 'show_image_in_single_mode';
    const SINGLE_IMAGE_MAX_HEIGHT              = self::PRE_IMG . 'single_image_max_height';
    const SINGLE_IMAGE_MAX_WIDTH               = self::PRE_IMG . 'single_image_max_width';

    /**
     * Section typography
     */
    const PRE_TYPO                             = 'typography/';
    const COLOR_CATEGORIES                     = self::PRE_TYPO . 'color_categories';
    const COLOR_LINES                          = self::PRE_TYPO . 'color_lines';
    const COLOR_PRICE                          = self::PRE_TYPO . 'color_price';
    const COLOR_PRICE_OLD                      = self::PRE_TYPO . 'color_price_old';
    const COLOR_SKU                            = self::PRE_TYPO . 'color_sku';
    const COLOR_STORE_NAME                     = self::PRE_TYPO . 'color_store_name';
    const COLOR_TEXT                           = self::PRE_TYPO . 'color_text';
    const COLOR_TITLE                          = self::PRE_TYPO . 'color_title';
    const FONT_BOLD                            = self::PRE_TYPO . 'font_bold';
    const FONT_REGULAR                         = self::PRE_TYPO . 'font_regular';
    const LIST_TITLE_MAX_CHARS_IN_LINE         = self::PRE_TYPO . 'list_title_max_chars_in_line';
    const LIST_TITLE_SIZE                      = self::PRE_TYPO . 'list_title_size';
    const SINGLE_TITLE_MAX_CHARS_IN_LINE       = self::PRE_TYPO . 'single_title_max_chars_in_line';
    const SINGE_TITLE_SIZE                     = self::PRE_TYPO . 'single_title_size';


    /**
     * Events
     */
    const EVENT_ELEMENT_RENDER_START  = 'glugox_pdf_element_render_start';
    const EVENT_ELEMENT_RENDER_END    = 'glugox_pdf_element_render_end';

    /**
     * Default
     */
    const BLOCK_DEFAULT_HEIGHT = 0;
    const CONTAINER_DEFAULT_HEIGHT = 0;

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
     * @var string
     */
    protected $pageLayout;


    /**
     * Current rendering process state
     *
     * @var \Glugox\PDF\Model\Page\State
     */
    protected $_state;


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
     * Config constructor.
     * @param \Glugox\PDF\Model\Layout\LayoutInterface $layout
     * @param State $state
     */
    public function __construct(
        \Glugox\PDF\Model\Layout\LayoutInterface $layout,
        \Glugox\PDF\Model\Page\State $state,
        \Magento\Config\Model\Config\Loader $configLoader,
        \Glugox\PDF\Model\Renderer\Data\BoundingBoxFactory $boundingBoxFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Glugox\PDF\Helper\Data $helper
    ) {
        $this->layout = $layout;
        $this->_state = $state;
        $this->_configLoader = $configLoader;
        $this->_boundingBoxFactory = $boundingBoxFactory;
        $this->_eventManager = $eventManager;
        $this->_objectManeger = $objectManager;
        $this->_helper = $helper;

        $this->load();
    }


    /**
     * Loads config data from database
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function load(){

        if(!$this->_isLoaded){
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

        $rendered = $rootRenderer->render();
        while (Element::NEW_PAGE_FLAG === $rendered){
            $rendered = $this->renderOnNewPage();
        }

        return $rendered;
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
     * @return \Glugox\PDF\Model\Page\State
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * @param \Glugox\PDF\Model\Page\State $state
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function setState($state)
    {
        $this->_state = $state;
        return $this;
    }


    /**
     * @param $event
     * @param array $data
     */
    public function dispachEvent( $event, $data=[] ){
        $element = $data["element"];
        $elName = $element->getName();
        switch ($event){
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
    public function getHelper(){
        return $this->_helper;
    }


    /**
     * @return string
     */
    public function getStoreName(){
        return $this->getHelper()->getStoreName();
    }


    /**
     * Registers current/last rendered element to the config,
     * so we know the next relativly available position, and
     * current page state relative to that element.
     */
    public function setCurrentRenderingElement( \Glugox\PDF\Model\Renderer\RendererInterface $element ){
        $this->_currentRenderingElement = $element;
    }

    /**
     * Returns current/last rendering element
     *
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getCurrentRenderingElement(){
        return $this->_currentRenderingElement;
    }


    /**
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $element
     */
    public function addRenderedElement( \Glugox\PDF\Model\Renderer\RendererInterface $element){
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

        $this->getState()->setX(0)->setY(0);

        $rootRenderer->setPdfPage($page);
        $rootRenderer->handleNewPage();
        
        
        

        return $page;
    }


    /**
     * @return \Zend_Pdf
     */
    public function renderOnNewPage(){

        $this->newPage();
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
    public function getLayout(){
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
    public function setPageSize( $value ){
        $this->_pageSize = $value;

    }

    /**
     * @return string
     */
    public function getPageSize(){
        return $this->_pageSize;
    }


    /**
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $element
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function createNewBoundingBoxFor( \Glugox\PDF\Model\Renderer\RendererInterface $element ){

        $style = $element->getStyle();
        $name = $element->getName();
        $parentBB = $element->hasParent() ? $element->getParent()->getBoundingBox() : $element->getConfig()->getBoundingBox();

        /**
         * Depending on position is absolute or relative
         * calculate real position of the element, using its
         * parent.
         */
        $newMaxWidth = $parentBB->getInnerWidth();
        //$newMaxHeight = $parentBB->getInnerHeight();

        $relX = 0; // relative position
        $relY = 0; // relative position

        // block type based values
        switch ($element->getType()){
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

        return $this->createNewBoundingBox( $x, $y, $width, $height, $element);
    }


    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function createPageBoundingBox(){

        $boundingBox = null;
        $sizeArr = \explode(":", $this->_pageSize);
        if(\count($sizeArr) >= 2){
            $boundingBox = $this->createNewBoundingBox(0,0,(int)$sizeArr[0],(int)$sizeArr[1]);
        }else{
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
        \Glugox\PDF\Model\Renderer\RendererInterface $element=null
    ){
        return $this->_boundingBoxFactory
            ->create([
                'x'=> $x,
                'y'=> $y,
                'width' => $width,
                'height'=> $height,
                'element' => $element
            ]);
    }

    /**
     * @var \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function getBoundingBox(){

        if(null == $this->_boundingBox){
            $this->_boundingBox = $this->createPageBoundingBox();

        }
        return$this->_boundingBox;
    }


    /**
     * @param string $styleSource
     * @return \Glugox\PDF\Model\Renderer\Data\Style
     */
    public function createStyle( $styleSource, \Glugox\PDF\Model\Renderer\RendererInterface $element ){
        $style = $this->_objectManeger->create('Glugox\PDF\Model\Renderer\Data\Style', ['source'=>$styleSource, 'element'=>$element]);
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
     * @param Collection $productCollection
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function setProductCollection(Collection $productCollection)
    {
        $this->_productCollection = $productCollection;
        return $this;
    }


}