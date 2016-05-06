<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer\Container\Product;


use Glugox\PDF\Model\Renderer\Data\Style;


class Item extends \Glugox\PDF\Model\Renderer\Container\AbstractRenderer
{

    /**
     * It actually sets the size of the block to estimated size!
     * As this is repeating item, we will suppose we don't need the
     * size estimation for the first item in the parent repeater.
     * Than every next repeating item will get the previous item size
     * as the estimated size.
     *
     * @return array
     */
    public function _estimateSize(){
        if( null === $this->_estimatedSize ){
            $this->_estimatedSize = [0,0];
        }
        if( $this->getParent() && $this->getParent()->getLastRenderedItem() ){
            $this->_estimatedSize = [
                $this->getParent()->getLastRenderedItem()->getBoundingBox()->getWidth(),
                $this->getParent()->getLastRenderedItem()->getBoundingBox()->getHeight()
            ];
        }
        if(!$this->getStyle()->get(Style::STYLE_HEIGHT)){
            $this->getBoundingBox()->setHeight($this->_estimatedSize[1]);
        }

        return $this->_estimatedSize;
    }

    /**
     * Updates children properties needed for rendering
     * like bounding box (x,y,width,height) after page state
     * is changed (like rendered new element)
     *
     * We are manipulating one repeater item children for all
     * repeater items, so we need to re set the children to
     * current repeater item on each update layout.
     */
    public function updateLayout()
    {
        if($this->hasChildren()){
            $this->setChildren($this->getChildren());
        }
        parent::updateLayout();
    }



    /**
     * @return \Zend_Pdf $pdf
     */
    /*public function _render()
    {

        // defines
        $bBox = $this->getBoundingBox();
        $style = $this->getStyle();
        $padding = $style->get(Style::STYLE_PADDING);
        $product = $this->getProduct();
        $helper = $this->getConfig()->getHelper();

        // get the image
        $imagePath = $helper->getProductImage($product);
        $image = \Zend_Pdf_Image::imageWithPath($imagePath);

        // calculate image size
        $sizeFactor = $image->getPixelWidth() / $image->getPixelHeight();
        $maxWidth = $bBox->getInnerWidth();
        $maxHeight = 0.6 * $bBox->getInnerHeight();
        if($maxWidth){
            $imageWidth = $maxWidth;
            $imageHeight = $imageWidth / $sizeFactor;;
            if($maxHeight && $imageHeight > $maxHeight){
                $imageHeight = $maxHeight;
                $imageWidth = $imageHeight * $sizeFactor;
            }
        }else if($maxWidth){
            $imageWidth = $maxWidth;
            $imageHeight = $imageWidth / $sizeFactor;
        }

        // define position
        $x1 = $bBox->getAbsX1() + $padding[3];
        $y1 = $bBox->getAbsY1() - $padding[0];


        // write image to page
        $this->getPdfPage()->drawImage($image,$x1, $y1 - $imageHeight , $x1 + $imageWidth, $y1 );
    }*/

}