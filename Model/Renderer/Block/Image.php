<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer\Block;


use Glugox\PDF\Model\Renderer\Data\Style;

class Image extends AbstractRenderer
{
    /**
     * @var string
     */
    protected $_src = null;

    /**
     * @return string
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
     * @return \Zend_Pdf $pdf
     */
    public function _render()
    {
        $style = $this->getStyle();
        $style->applyToPage($this->getPdfPage());

        if (null !== $this->getSrc()) {

            $bBox = $this->getBoundingBox();
            $padding = $style->get(Style::STYLE_PADDING);
            $image = \Zend_Pdf_Image::imageWithPath($this->getSrc());
            $sizeFactor = $image->getPixelWidth() / $image->getPixelHeight();

            $imageStyleHeight = $this->getStyle()->get(Style::STYLE_HEIGHT, 0);
            $imageStyleWidth = $this->getStyle()->get(Style::STYLE_WIDTH, 0);

            $maxWidth = \max($bBox->getInnerWidth(), $imageStyleWidth);
            $maxHeight = \max($bBox->getInnerHeight(), $imageStyleHeight);

            if($maxHeight){
                $imageHeight = $maxHeight;
                $imageWidth = $sizeFactor * $imageHeight;
                if($maxWidth && $imageWidth > $maxWidth){
                    $imageWidth = $maxWidth;
                    $imageHeight = $imageWidth / $sizeFactor;
                }
            }else if($maxWidth){
                $imageWidth = $maxWidth;
                $imageHeight = $imageWidth / $sizeFactor;
            }else{
                $imageHeight = 30;
                $imageWidth = $sizeFactor * $imageHeight;
            }



            $x1 = $bBox->getAbsX1() + $padding[3];
            $y1 = $bBox->getAbsY1() - $padding[0];

            $align = $style->get(Style::STYLE_TEXT_ALIGN);

            switch ($align[0]){
                case Style::ALIGN_TOP:
                    // aleady set
                    break;
                case Style::ALIGN_BOTTOM:
                    $y1 = $bBox->getAbsY2() + $padding[2];
                    break;
            }

            switch ($align[1]){
                case Style::ALIGN_LEFT:
                    // aleady set
                    break;
                case Style::ALIGN_RIGHT:
                    $x1 = $bBox->getAbsX2() - $padding[1] - $imageWidth;
                    break;
            }




            // write image to page
            $this->getPdfPage()->drawImage($image,$x1, $y1 - $imageHeight , $x1 + $imageWidth, $y1 );

            //if(!$this->getStyle()->get(Style::STYLE_HEIGHT)){
                $bBox->setHeight($imageHeight);
            //}
            //if(!$this->getStyle()->get(Style::STYLE_WIDTH)){
                $bBox->setWidth($imageWidth);
            //}
        }

    }


}