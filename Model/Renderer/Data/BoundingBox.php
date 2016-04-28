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
            $parentX = $this->getParentBB()->getAbsX1();
            $this->setAbsX1($parentX + $x);
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
            $parentY = $this->getParentBB()->getAbsY1();
            $this->setAbsY1($parentY - $y);
            if(null !== $this->absY2){
                $this->height = $this->absY1 - $this->absY2; // absY1 is larger in bottom-left coo system
            }
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
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
    public function getHeight()
    {
        return $this->height;
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
            if(null !== $this->absX2){
                $this->width = $this->absX2 - $this->absX1;
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
            if(null !== $this->absY2){
                $this->height = $this->absY1 - $this->absY2;
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
    protected function getParentBB(){

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

    /**
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $element
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     *
     * @deprecated Set the element only in constructor
     */
    /*public function setElement($element)
    {
        $this->_element = $element;
        return $this;
    }*/

    

}