<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer\Data;


use Glugox\PDF\Exception\PDFException;

class BoundingBox
{

    /**
     * @var int
     */
    private $width;
    /**
     * @var int
     */
    private $height;

    /**
     * Absolute x1 position in bottom-left coo system
     * Ready for drawing.
     *
     * @var int
     */
    private $absX1;

    /**
     * Absolute x2 position in bottom-left coo system
     * Ready for drawing.
     *
     * @var int
     */
    private $absX2;

    /**
     * Absolute y1 position in bottom-left coo system
     * Ready for drawing.
     *
     * @var int
     */
    private $absY1;

    /**
     * Absolute y2 position in bottom-left coo system
     * Ready for drawing.
     *
     * @var int
     */
    private $absY2;


    /**
     * Element occupied width after it is rendered, it is the
     * real x space occupied of this element in
     * the parent container.
     *
     * @var int
     */
    private $_occupiedWidth = 0;

    /**
     * Element occupied height abs position after it is rendered, it is the
     * real y space occupied of this element in
     * the parent container.
     *
     * @var int
     */
    private $_occupiedHeight = 0;

    /**
     * @var bool
     */
    private $_canIncreaseHeight = true;

    /**
     * @var bool
     */
    private $_canIncreaseWidth = true;


    /**
     * @var bool
     */
    private $_isFloatLeft = null;

    /**
     * @var bool
     */
    private $_isFloatRight = null;


    /**
     * @var \Glugox\PDF\Model\Renderer\RendererInterface
     */
    protected $_element = null;


    /**
     * @var \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    protected $_parentBB = null;


    /**
     * @var bool
     */
    protected $_isReadOnly = false;


    /**
     * Original state values
     *
     * @var array
     */
    protected $_origState;


    /**
     * BoundingBox constructor.
     *
     * @param int $x Relative x position
     * @param int $y Relative y position
     * @param int $width
     * @param int $height
     */
    public function __construct(
        $x,
        $y,
        $width,
        $height,
        \Glugox\PDF\Model\Renderer\RendererInterface $element=null
    )
    {
        $this->_isReadOnly = $element === null;
        $this->_element = $element;

        if(!$this->_isReadOnly){
            $this->setRelX1($x);
            $this->setRelY1($y);
            $this->setWidth($width);
            $this->setHeight($height);
        }else{
            $this->width = $width;
            $this->height = $height;
            $this->absX1 = $x;
            $this->absX2 = $x + $width;
            $this->absY1 = $height - $y;
            $this->absY2 = $this->absY1 - $height;
        }

        $this->_origState = [ $this->absX1, $this->absX2, $this->absY1, $this->absY2, $this->width, $this->height ];

    }


    /**
     * Reseting
     */
    public function reset(){

        $this->absX1   = $this->_origState[0];
        $this->absX2   = $this->_origState[1];
        $this->absY1   = $this->_origState[2];
        $this->absY2   = $this->_origState[3];
        $this->width   = $this->_origState[4];
        $this->height  = $this->_origState[5];

        $this->_occupiedWidth = $this->_occupiedHeight = 0;
    }


    /**
     * Updates properties needed for rendering
     * like bounding box (x,y,width,height) after page state
     * is changed (like rendered new element)
     */
    public function updateLayout(){
        //
    }

    /**
     * @return int
     */
    public function getRelX1()
    {
        return $this->getAbsX1() - $this->getParentBB()->getAbsX1();
    }

    /**
     * @param int $x
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setRelX1($x)
    {
        if(!$this->_isReadOnly){
            $offsetX = 0;
            $parent = $this->getElement()->getParent();
            if($parent){
                $parentPadding = $parent->getStyle()->get(Style::STYLE_PADDING);
                $currentMargin = $this->getElement()->getStyle()->get(Style::STYLE_MARGIN);
                $offsetX = $parentPadding[3] + $currentMargin[3]; // padding left + margin left
            }
            $parentX = $this->getParentBB()->getAbsX1();
            $this->setAbsX1($parentX + $x + $offsetX);
        }
        return $this;
    }

    /**
     * Basic transformation from bottom-left to top-left
     *
     * @return int
     */
    public function getRelY1()
    {
        return $this->getParentBB()->getAbsY1() - $this->getAbsY1();
    }

    /**
     * @param int $y
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setRelY1($y)
    {
        if(!$this->_isReadOnly){
            $offsetY = 0;
            $parent = $this->getElement()->getParent();
            if($parent){
                $parentPadding = $parent->getStyle()->get(Style::STYLE_PADDING);
                $currentMargin = $this->getElement()->getStyle()->get(Style::STYLE_MARGIN);
                $offsetY = $parentPadding[0] + $currentMargin[0]; // padding top + margin top
            }
            $parentY = $this->getParentBB()->getAbsY1();
            $this->setAbsY1($parentY - $y - $offsetY);
        }
        return $this;
    }


    /**
     * Moves the bounding box position
     *
     * @param  int $horPos (-) => LEFT, (+) => RIGHT
     * @param int $vertPos (-) => UP, (+) => DOWN
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function move( $horPos, $vertPos ){

        $relX = $this->getRelX1() + $horPos;
        $relY = $this->getRelY1() + $vertPos;

        $this->setRelX1($relX);
        $this->setRelY1($relY);

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth( $includePadding=true, $includeMargin=false )
    {
        $width = $this->width;
        if(!$width){
            return 0;
        }
        if($this->getElement()){
            if(!$this->getElement()->getStyle()->canDisplay()){
                return 0;
            }
            if($includePadding){
                $padding = $this->getElement()->getStyle()->get(Style::STYLE_PADDING);
                $width -= ( $padding[1] + $padding[3] );
            }
            if($includeMargin){
                $margin = $this->getElement()->getStyle()->get(Style::STYLE_MARGIN);
                $width += ( $margin[1] + $margin[3] );
            }
        }

        return $width;
    }

    /**
     * @param int $width
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setWidth($width)
    {
        if(!$this->_isReadOnly){
            $this->width = $width;
            if(null !== $this->absX1){
                $this->absX2 = $this->absX1 + $width;
            }
        }
        return $this;
    }



    /**
     * @return int
     */
    public function getHeight( $includePadding=true, $includeMargin=false )
    {
        $height = $this->height;
        if(!$height){
            return 0;
        }
        if($this->getElement()){
            if(!$this->getElement()->getStyle()->canDisplay()){
                return 0;
            }
            if($includePadding){
                $padding = $this->getElement()->getStyle()->get(Style::STYLE_PADDING);
                $height -= ( $padding[0] + $padding[2] );
            }
            if($includeMargin){
                $margin = $this->getElement()->getStyle()->get(Style::STYLE_MARGIN);
                $height += ( $margin[0] + $margin[2] );
            }
        }

        return $height;
    }
    /**
     * @param int $height
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setHeight($height)
    {
        if(!$this->_isReadOnly){
            $this->height = $height;
            if(null !== $this->absY1){
                $this->absY2 = $this->absY1 - $height;
            }
        }
        return $this;
    }


    /**
     * Height of the element from outer bounding box persperctive
     *
     * @return int
     */
    public function getOuterWidth(){
        return $this->getWidth(false, true);
    }


    /**
     * Height of the element from outer bounding box persperctive
     *
     * @return int
     */
    public function getOuterHeight(){
        return $this->getHeight(false, true);
    }


    /**
     * Height of the element from inner bounding box persperctive
     *
     * @return int
     */
    public function getInnerWidth(){
        return $this->getWidth(true, false);
    }


    /**
     * Height of the element from outer bounding box persperctive
     *
     * @return int
     */
    public function getInnerHeight(){
        return $this->getHeight(true, false);
    }


    /**
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * absX1 is x position in bottom left coo system.
     *
     * @return int
     */
    public function getAbsX1()
    {
        return $this->absX1;
    }

    /**
     * @param int $x1
     *
     * absX1 is x position in bottom left coo system.
     *
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setAbsX1($x1)
    {
        if(!$this->_isReadOnly){
            $this->absX1 = $x1;
            if(null !== $this->width){
                $this->absX2 = $this->absX1 + $this->width;
            }
        }
        return $this;
    }

    /**
     * @return int
     *
     * x2 is x+height position in bottom left coo system.
     */
    public function getAbsX2()
    {
        return $this->absX2;
    }

    /**
     * x2 is x+height position in bottom left coo system.
     *
     * @param int $x2
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setAbsX2($x2)
    {
        if(!$this->_isReadOnly){
            $this->absX2 = $x2;
            if(null !== $this->absX1){
                $this->width = $this->absX2 - $this->absX1;
            }
        }
        return $this;
    }

    /**
     * y1 is y position in bottom left coo system.
     *
     * @return int
     */
    public function getAbsY1()
    {
        return $this->absY1;
    }

    /**
     * y1 is y position in bottom left coo system.
     *
     * @param int $y1
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setAbsY1($y1)
    {
        if(!$this->_isReadOnly){
            $this->absY1 = $y1;
            if(null !== $this->height){
                $this->absY2 = $this->absY1 - $this->height;
            }
        }
        return $this;
    }

    /**
     * y2 is y+height position in bottom left coo system.
     *
     * @return int
     */
    public function getAbsY2()
    {
        return $this->absY2;
    }

    /**
     * y2 is y+height position in bottom left coo system.
     *
     * @param int $y2
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setAbsY2($y2)
    {
        if(!$this->_isReadOnly){
            $this->absY2 = $y2;
            if(null !== $this->absY1){
                $this->height = $this->absY1 - $this->absY2;
            }
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getOccupiedWidth()
    {
        return $this->_occupiedWidth;
    }

    /**
     * @return int
     */
    public function getOccupiedHeight()
    {
        return $this->_occupiedHeight;
    }

    /**
     * @param int $occupiedWidth
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setOccupiedWidth($occupiedWidth)
    {
        $this->_occupiedWidth = $occupiedWidth;
        return $this;
    }

    /**
     * @param int $occupiedHeight
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setOccupiedHeight($occupiedHeight)
    {
        $this->_occupiedHeight = $occupiedHeight;
        return $this;
    }


    /**
     * @param $height
     * @return bool
     * @throws PDFException
     */
    public function canOccupyHeight($height){
        if($this->getCanIncreaseHeight()){
            return true;
        }
        $totalHeight = $this->_occupiedHeight + $height;
        return $this->getInnerHeight() >= $totalHeight;
    }

    /**
     * @param $height
     * @return bool
     * @throws PDFException
     */
    public function canOccupyWidth($width){
        $maxWidth = $this->getInnerWidth();
        if($this->getCanIncreaseWidth()){
            $maxWidth = $this->getElement()->getConfig()->getBoundingBox()->getInnerWidth();
        }
        $totalWidth = $this->_occupiedWidth + $width;
        return $maxWidth >= $totalWidth;
    }


    /**
     * Check if an element can fit in
     * this bounding box.
     */
    public function canOccupyElement( \Glugox\PDF\Model\Renderer\RendererInterface $element, $includeOccSize = true ){

        $maxWidth = $this->getInnerWidth();
        $maxHeight = $this->getInnerHeight();
        if($this->getCanIncreaseWidth() && $this->getCanIncreaseHeight()){
            $cBox = $this->getElement()->getConfig()->getBoundingBox();
            $maxHeight = $cBox->getInnerHeight();
            $maxWidth = $cBox->getInnerWidth();
        }

        $bBox = $element->getBoundingBox();
        $hToOccupy = $bBox->getOuterHeight();
        $wToOccupy = $bBox->getOuterWidth();
        $style = $element->getStyle();

        $includeOccSizeK = (int)$includeOccSize;

        if( !$style->get(Style::STYLE_FLOAT) ){
            $totalHeight = $includeOccSizeK*$this->_occupiedHeight + $hToOccupy;
            return $this->getInnerHeight() >= $totalHeight;
        }else{

            $totalWidth = $includeOccSizeK*$this->_occupiedWidth + $wToOccupy;
            $totalHeight = $includeOccSizeK*$this->_occupiedHeight + $hToOccupy;


            if( $maxHeight >= $totalHeight){
                return true;
            }else{
                return $maxWidth >= $totalWidth;
            }

        }

    }


    /**
     * Sets the occupied space (width/height) of the box
     * inside the parent element after being rendered.
     *
     * @param int $width
     * @param int $height
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function occupyPosition( $width=0, $height=0, $includeMargins=true ){

        if(!$this->_isReadOnly){

            $oldW = $this->_occupiedWidth;
            $oldH = $this->_occupiedHeight;

            $name = $this->getElement()->getName();
            $style = $this->getElement()->getStyle();

            // add the margins to width/height to occupy
            if($includeMargins){
                $margin = $style->get(Style::STYLE_MARGIN);
                if($width > 0){
                    $width += ( $margin[1] + $margin[3] );
                }
                if($height > 0){
                    $height += ( $margin[0] + $margin[2] );
                }
            }
            
            $this->_occupiedWidth += $width;
            $this->_occupiedHeight += $height;
        }
        
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCanIncreaseHeight()
    {
        return $this->_canIncreaseHeight;
    }

    /**
     * @param boolean $canIncreaseHeight
     */
    public function setCanIncreaseHeight($canIncreaseHeight)
    {
        $this->_canIncreaseHeight = $canIncreaseHeight;
    }

    /**
     * @return boolean
     */
    public function getCanIncreaseWidth()
    {
        return $this->_canIncreaseWidth;
    }

    /**
     * @param boolean $canIncreaseWidth
     */
    public function setCanIncreaseWidth($canIncreaseWidth)
    {
        $this->_canIncreaseWidth = $canIncreaseWidth;
    }

    /**
     * @return boolean
     */
    public function getIsFloatLeft()
    {
        return null === $this->_isFloatLeft ?
            $this->getElement()->getStyle()->isFloatLeft() :
            $this->_isFloatLeft;
    }

    /**
     * @param boolean $isFloatLeft
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setIsFloatLeft($isFloatLeft)
    {
        $this->_isFloatLeft = $isFloatLeft;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsFloatRight()
    {
        return null === $this->_isFloatRight ?
            $this->getElement()->getStyle()->isFloatRight() :
            $this->_isFloatRight;
    }

    /**
     * @param boolean $isFloatRight
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function setIsFloatRight($isFloatRight)
    {
        $this->_isFloatRight = $isFloatRight;
        return $this;
    }




    /**
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function getParentBB(){

        if( null === $this->_parentBB ){
            if( $this->_element && $this->_element->getParent() && $this->_element->getParent()->getBoundingBox() ){
                $this->_parentBB = $this->_element->getParent()->getBoundingBox();
            }else if($this->_element){
                $this->_parentBB = $this->_element->getConfig()->getBoundingBox();
            }else{
                throw new PDFException(__("Could not found parent bounding box for element: '%1'", $this->_element->getName()));
            }
        }

        return $this->_parentBB;

    }


    

}