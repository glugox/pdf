<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer\Block\Product;


use Glugox\PDF\Model\Renderer\Block\AbstractRenderer;
use Glugox\PDF\Model\Renderer\Data\Style;

class Media extends AbstractRenderer
{
    /**
     * Initializes data needed for rendering
     * of this element.
     */
    public function initialize(\Glugox\PDF\Model\Page\Config $config = null)
    {
        parent::initialize($config);


    }


    /**
     * @return \Zend_Pdf $pdf
     */
    public function _render()
    {
        $product = $this->getConfig()->getProduct();
        if(!$product || !$product instanceof \Magento\Catalog\Model\Product){
           if($this->getParent()){
               $product = $this->getParent()->getSrc();
           }
        }

        $bBox = $this->getBoundingBox();
        $helper = $this->getConfig()->getHelper();
        $style = $this->getStyle();
        $padding = $style->get(Style::STYLE_PADDING);


        $imagePath = $helper->getProductImage($product);

        // define image resource
        $image = \Zend_Pdf_Image::imageWithPath($imagePath);
        $sizeFactor = $image->getPixelWidth() / $image->getPixelHeight();

        /**
         * Define maximums
         */
        //$maxWidth = $helper->getConfigObject()->getSingleImageMaxWidth();
        //$maxHeight = $helper->getConfigObject()->getSingleImageMaxHeight();
        $maxWidth = $style->get(Style::STYLE_WIDTH);
        $maxHeight = $style->get(Style::STYLE_HEIGHT);

        if($style->get(Style::STYLE_WIDTH) && $bBox->getInnerWidth()){
            $maxWidth = $maxWidth ? \min($maxWidth, $bBox->getInnerWidth()) : $bBox->getInnerWidth();
        }
        if($style->get(Style::STYLE_HEIGHT) && $bBox->getInnerHeight()){
            $maxHeight = $maxHeight ? \min($maxHeight, $bBox->getInnerHeight()) : $bBox->getInnerHeight();
        }

        /**
         * Prefer width definition for calculation
         * of definite size.
         */
        if ($maxWidth) {
            $imageWidth = $maxWidth;
            $imageHeight = $imageWidth / $sizeFactor;
            if ($maxHeight && $imageHeight > $maxHeight) {
                $imageHeight = $maxHeight;
                $imageWidth = $sizeFactor * $imageHeight;
            }
        } else if ($maxHeight) {
            $imageHeight = $maxHeight;
            $imageWidth = $sizeFactor * $imageHeight;
        } else {

            $bBox->setWidth(0)->setHeight(0);
            return false;
            /*$imageHeight = 320;
            $imageWidth = $sizeFactor * $imageHeight;  */
        }

        /**
         * Define position
         */
        $x1 = $bBox->getAbsX1() + $padding[3];
        $y1 = $bBox->getAbsY1() - $imageHeight - $padding[0];

        // Write image to page
        $this->getPdfPage()->drawImage($image, $x1, $y1, $x1 + $imageWidth, $y1 + $imageHeight);

        /**
         * Update bounding box size
         */
        $bBox->setWidth($imageWidth)->setHeight($imageHeight);
    }


}