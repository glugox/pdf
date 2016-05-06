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
use Glugox\PDF\Model\Layout\Layout;
use Glugox\PDF\Model\Renderer\Data\Style;
use Glugox\PDF\Model\Page\Config;

abstract class Element implements RendererInterface
{


    /**
     * Flags
     */
    const NEW_PAGE_FLAG = 'new_page_flag';


    /**
     * @var string Element name in layout
     */
    protected $_name;


    /**
     * @var string Element type (block,container,...)
     */
    protected $_type;

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
     * Source for an element, depending on what
     * the element is, ex textarea source text or
     * src image for image block
     *
     * @var mixed
     */
    protected $_src = null;


    /**
     * @var bool
     */
    protected $_debugMode = null;


    /**
     * @var \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    protected $_boundingBox = null;


    /**
     * @var bool
     */
    protected $_isRendered = false;


    /**
     * @var int
     */
    protected $_currX = 0;

    /**
     * @var int
     */
    protected $_currY = 0;


    /**
     * @var int
     */
    protected $_bottomMostY = null;


    /**
     * @var array
     */
    protected $_estimatedSize;


    /**
     * Element constructor.
     *
     * @param string $type
     * @param int $order
     * @param string $style
     */
    public function __construct($type, $order = 0, $style = '', $src = null)
    {
        $this->_type = $type;
        $this->_order = $order;
        $this->_styleSource = $style;
        $this->_src = $src;
    }


    /**
     * Initializes data needed for rendering
     * of this element.
     */
    public function initialize(\Glugox\PDF\Model\Page\Config $config = null)
    {

        // it is important that we call set style on initialize as for inheriting
        // styles, the element must have parent already set if exists anyway.
        $this->setStyle($this->_styleSource);
        $this->_parseSource();
    }


    /**
     * Updates properties needed for rendering
     * like bounding box (x,y,width,height) after page state
     * is changed (like rendered new element)
     */
    public function updateLayout()
    {
        //
    }

    /**
     * @return \Zend_Pdf
     */
    public function getPdf()
    {
        if (!$this->_pdf && $this->hasParent()) {
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
        if (!$this->_pdfPage && $this->hasParent()) {
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
     * Actions after new pdf page is created
     */
    public function handleNewPage()
    {
        $this->getBoundingBox()->reset();

        /**
         * mark requesting the page from the parent
         * element again
         */
        if ($this->hasParent()) {
            $this->_pdfPage = null;
        }

    }


    /**
     * Config getter
     *
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function getConfig()
    {
        if (!$this->_config && $this->hasParent()) {
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
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setName($name)
    {
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
        if (!$style instanceof Style) {
            $style = $this->getConfig()->createStyle($style, $this);
        }
        $this->_style = $style;
        return $this;
    }


    /**
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function getBoundingBox()
    {
        if (null === $this->_boundingBox) {
            $this->_boundingBox = $this->getConfig()->createNewBoundingBoxFor($this);
        }
        return $this->_boundingBox;
    }

    /**
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setBoundingBox(\Glugox\PDF\Model\Renderer\Data\BoundingBox $boundingBox)
    {
        $this->_boundingBox = $boundingBox;
    }


    /**
     * @return boolean
     */
    public function isDebugMode()
    {
        if (null === $this->_debugMode) {
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
     * @return boolean
     */
    public function getIsRendered()
    {
        return $this->_isRendered;
    }

    /**
     * @param boolean $isRendered
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setIsRendered($isRendered)
    {
        $this->_isRendered = $isRendered;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrX()
    {
        return $this->_currX;
    }

    /**
     * @param int $currX
     */
    public function setCurrX($currX)
    {
        $this->_currX = $currX;
    }

    /**
     * @return int
     */
    public function getCurrY()
    {
        return $this->_currY;
    }

    /**
     * @param int $currY
     */
    public function setCurrY($currY)
    {
        $this->_currY = $currY;
    }


    /**
     * @throws PDFException
     */
    public function checkSize()
    {
        // Only blocks can occupy position, containers occupied position
        // is calculated in updateLayout methods

        $newPageFlag = false;
        $this->_estimateSize();
        $bBox = $this->getBoundingBox();
        if ($bBox->getAbsY2() < $this->getBottomMostY()) {
            $newPageFlag = true;
        } else {
            return true;
        }


        if ($newPageFlag) {
            return self::NEW_PAGE_FLAG;
        } else {
            return false;
        }
    }


    /**
     * @return \Zend_Pdf|string
     */
    public function render()
    {

        $name = $this->getName();
        $this->handleRenderStart();
        $bBox = $this->getBoundingBox();

        if ($this->getStyle()->canDisplay()) {

            // checking values
            //if ($this->isBlock()) {

            $sizeResult = $this->checkSize();
            if (true === $sizeResult) {
                //
            } else if ($sizeResult === self::NEW_PAGE_FLAG) {
                return self::NEW_PAGE_FLAG;
            }
            // }

            // real rendering
            $renderResult = $this->_render();
            if (self::NEW_PAGE_FLAG === $renderResult) {
                return self::NEW_PAGE_FLAG;
            }

            $bBox->occupyPosition($bBox->getWidth(), $bBox->getHeight());

            // debug
            if ($this->isDebugMode()) {
                //$this->_drawBoundingBox();
            }

        }


        // rendering end
        // set this is rendered
        $this->handleRenderEnd();

        return $this->getPdf();
    }


    /**
     * Overriding render method
     */
    public function _render()
    {
        // parent classes should override this method.
    }


    public function _estimateSize()
    {
        // parent classes should override this method.
    }


    /**
     * @return RendererInterface
     */
    public function handleRenderStart()
    {

        $config = $this->getConfig();
        if (!$config) {
            throw new PDFException(__("Element '{$this->_name}'' trying to render, but no config found!"));
        }

        // rendering start
        $config->dispachEvent(Config::EVENT_ELEMENT_RENDER_START, ["element" => $this]);
        $pdf = $this->getPdf();
        if (!$pdf) {
            throw new PDFException(__("Element '{$this->_name}'' trying to render, but root rendrer is not initialized!"));
        }
        return $this;
    }


    /**
     * @return RendererInterface
     */
    public function handleRenderEnd()
    {

        $config = $this->getConfig();
        $this->setIsRendered(true);
        $config->dispachEvent(Config::EVENT_ELEMENT_RENDER_END, ["element" => $this]);

        return $this;
    }


    /**
     * Draws bounding box for the current
     * element for debugging purposes.
     */
    protected function _drawBoundingBox()
    {

        $page = $this->getPdfPage();
        $page->setFillColor(new \Zend_Pdf_Color_Html($this->getStyle()->get(Style::STYLE_BG_COLOR)));
        $page->setAlpha(0.3);
        $page->setFont($this->getStyle()->getFontResource(), 12);
        $bBox = $this->getBoundingBox();


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


        if ($this->isBlock()) {
            $page->setFillColor(new \Zend_Pdf_Color_Html("#cccccc"));
            $page->drawText($this->getName(), $bBox->getAbsX1(), $bBox->getAbsY1());
        }


    }

    /**
     * Returns bottom most y this element
     * can go.
     *
     * @return double
     */
    public function getBottomMostY()
    {
        if (null == $this->_bottomMostY) {
            $this->_bottomMostY = $this->getConfig()->getBoundingBox()->getAbsY2();
            $parent = $this->getParent();
            while ($parent) {
                $padding = $parent->getStyle()->get(Style::STYLE_PADDING_BOTTOM, 0);
                $this->_bottomMostY += $padding;
                $parent = $parent->getParent();
            }
        }
        return $this->_bottomMostY;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }


    /**
     * Returns true if this element is block type element.
     *
     * @return boolean
     */
    public function isBlock()
    {
        return $this->_type === Layout::TYPE_BLOCK;
    }


    /**
     * Returns true if this element is container type element.
     *
     * @return boolean
     */
    public function isContainer()
    {
        return $this->_type === Layout::TYPE_CONTAINER;
    }

    /**
     * @return mixed
     */
    public function getSrc()
    {
        return $this->_src;
    }

    /**
     * @param string $src
     */
    public function setSrc($src)
    {
        $this->_src = $src;
    }


    /**
     * @param $src
     */
    protected function _parseSource()
    {
        if (!empty($this->_src)) {
            if (is_string($this->_src) && strpos($this->_src, "config:") === 0) {
                $configPath = substr($this->_src, 7);
                $config = $this->getConfig();
                if (\method_exists($config, $configPath)) {
                    $this->_src = \call_user_func_array([$config, $configPath], []);
                } else {
                    $this->_src = $this->getConfig()->getHelper()->getConfig($configPath);
                }
            } else if (is_string($this->_src) && strpos($this->_src, "parent:") === 0) {
                $configPath = substr($this->_src, 7);
                $parent = $this->getParent();
                if ($parent && \method_exists($parent, $configPath)) {
                    $this->_src = \call_user_func_array([$parent, $configPath], []);
                }
            }
        }
    }


    /**
     * Prepares string fro drawing into pdf
     *
     * @param type $str
     * @return type
     */
    public function prepareTextForDrawing($str)
    {
        $str = \strip_tags($str, '<a>');
        return \html_entity_decode($str);
    }


    /**
     * @return array
     */
    public function getShortInfo()
    {
        $data = ["name" => $this->_name, "class" => \get_called_class()];
        if ($this->hasParent()) {
            $data["parent"] = $this->getParent()->getName();
        }
        return $data;
    }


}