<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer;


use Glugox\PDF\Exception\PDFException;
use Glugox\PDF\Model\Renderer\Data\Style;
use Glugox\PDF\Model\Page\Config;

abstract class Element implements RendererInterface
{

    /**
     * @var string Element name in layout
     */
    protected $_name;

    /**
     * Parent renderer element
     *
     * @var \Glugox\PDF\Model\Renderer\RendererInterface
     */
    protected $_parent = null;

    /**
     * Page pdf Config
     *
     * @var \Glugox\PDF\Model\Page\Config
     */
    protected $_config = null;


    /**
     * @var \Zend_Pdf
     */
    protected $_pdf = null;


    /**
     * @var \Zend_Pdf_Page
     */
    protected $_pdfPage = null;


    /**
     * Ordering of this element inside its parent element
     *
     * @var int
     */
    protected $_order = 0;


    /**
     * Element style, 
     * 
     * @var Style
     */
    protected $_style = null;

    /**
     * Element style source string,
     *
     * @var string
     */
    protected $_styleSource = '';


    /**
     * @var bool
     */
    protected $_debugMode = null;


    /**
     * @var \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    protected $_boundingBox = null;



    /**
     * Element constructor.
     *
     * @param null $order
     * @param Style|string|null $style
     */
    public function __construct( $order = 0, $style = '' )
    {
        $this->_order = $order;
        $this->_styleSource = $style;
    }


    /**
     * Initializes data needed for rendering
     * of this element.
     */
    public function initialize(\Glugox\PDF\Model\Page\Config $config = null){

        // it is important that we call set style on initialize as for inheriting
        // styles, the element must have parent already set if exists anyway.
        $this->setStyle($this->_styleSource);
    }


    /**
     * Updates properties needed for rendering
     * like bounding box (x,y,width,height) after page state
     * is changed (like rendered new element)
     */
    public function updateLayout(){
        $lastRenderingEl = $this->getConfig()->getCurrentRenderingElement();
        // TODO
    }

    /**
     * @return \Zend_Pdf
     */
    public function getPdf()
    {
        if(!$this->_pdf && $this->hasParent()){
            $this->_pdf = $this->getParent()->getPdf();
        }
        return $this->_pdf;
    }

    /**
     * @param \Zend_Pdf $pdf
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setPdf($pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * @return \Zend_Pdf_Page
     */
    public function getPdfPage()
    {
        if(!$this->_pdfPage && $this->hasParent()){
            $this->_pdfPage = $this->getParent()->getPdfPage();
        }
        return $this->_pdfPage;
    }

    /**
     * @param \Zend_Pdf_Page $pdfPage
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setPdfPage($pdfPage)
    {
        $this->_pdfPage = $pdfPage;
        return $this;
    }


    /**
     * Config getter
     *
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function getConfig()
    {
        if(!$this->_config && $this->hasParent()){
            $this->_config = $this->getParent()->getConfig();
        }
        return $this->_config;
    }

    /**
     * Config setter
     *
     * @param \Glugox\PDF\Model\Page\Config $config
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setConfig(\Glugox\PDF\Model\Page\Config $config)
    {
        $this->_config = $config;
        return $this;
    }


    /**
     * @return string Name
     */
    public function getName(){
        return $this->_name;
    }

    /**
     * @param string $name
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setName($name){
        $this->_name = $name;
        return $this;
    }


    /**
     * Parent renderer element getter
     *
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Parent renderer element setter
     *
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $name
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setParent($renderer)
    {
        $this->_parent = $renderer;
        return $this;
    }


    /**
     * Only root should not have parent renderer
     *
     * @return boolean
     */
    public function hasParent()
    {
        return null !== $this->_parent;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @param int $order
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setOrder($order)
    {
        $this->_order = (int)$order;
        return $this;
    }

    /**
     * @return Style
     */
    public function getStyle()
    {
        return $this->_style;
    }

    /**
     * @param string $style
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setStyle($style)
    {
        if( !$style instanceof Style){
            $style = Style::getInstance($style, $this);
        }
        $this->_style = $style;
        return $this;
    }


    /**
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function getBoundingBox(){
        if(null === $this->_boundingBox){
            $this->_boundingBox = $this->getConfig()->createNewBoundingBoxFor($this);
        }
        return $this->_boundingBox;
    }

    /**
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setBoundingBox(\Glugox\PDF\Model\Renderer\Data\BoundingBox $boundingBox){
        $this->_boundingBox = $boundingBox;
    }


    /**
     * @return boolean
     */
    public function isDebugMode()
    {
        if(null === $this->_debugMode){
            $this->_debugMode = $this->hasParent() ? $this->getParent()->isDebugMode() : $this->getConfig()->getData(Config::DEBUG_MODE);
        }
        return $this->_debugMode;
    }

    /**
     * @param boolean $debugMode
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setDebugMode($debugMode)
    {
        $this->_debugMode = $debugMode;
        return $this;
    }




    /**
     * @return \Zend_Pdf $pdf
     */
    public function render()
    {

        $config = $this->getConfig();
        if( !$config ){
            throw new PDFException(__("Element '{$this->_name}'' trying to render, but no config found!"));
        }

        // rendering start
        $config->dispachEvent(Config::EVENT_ELEMENT_RENDER_START, ["element" => $this]);

        $pdf = $this->getPdf();
        if(!$pdf){
            throw new PDFException(__("Element '{$this->_name}'' trying to render, but root rendrer is not initialized!"));
        }

        // debug
        if($this->isDebugMode()){
            $this->_drawBoundingBox();
        }

        // rendering end
        $config->dispachEvent(Config::EVENT_ELEMENT_RENDER_END, ["element" => $this]);

        return $this->getPdf();
    }



    /**
     * Draws bounding box for the current
     * element for debugging purposes.
     */
    protected function _drawBoundingBox(){

        $page = $this->getPdfPage();
        $bBox = $this->getBoundingBox();
        $page->setFillColor(new \Zend_Pdf_Color_Html($this->getStyle()->get(Style::STYLE_BG_COLOR, "#f9f9f9")));

        /**
         * If we want to draw rectangle in top left corner with margin 10: [x:10; y:10; width:300; height:100],
         * and the pdf page size is 595:842
         * the bounding box will transform those values into:
         * [x1:10; y1:832; x2:300; y2:100]
         */
        $page->drawRectangle(
            $bBox->getAbsX1(), // x:10
            $bBox->getAbsY1(), // y:832
            $bBox->getAbsX2(), // x2:310
            $bBox->getAbsY2(), // y2:732
            \Zend_Pdf_Page::SHAPE_DRAW_FILL
        );



    }


    /**
     * @return array
     */
    public function getShortInfo()
    {
        $data = ["name" => $this->_name, "class" => \get_called_class() ];
        if($this->hasParent()){
            $data["parent"] = $this->getParent()->getName();
        }
        return $data;
    }



}