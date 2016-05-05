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
use Glugox\PDF\Model\Renderer\Element;

class TextArea extends AbstractRenderer
{

    /**
     * @return \Zend_Pdf $pdf
     */
    public function _render()
    {
        $bBox = $this->getBoundingBox();
        $style = $this->getStyle();
        $padding = $style->get(Style::STYLE_PADDING);
        $style->applyToPage($this->getPdfPage());

        $textWidth = $style->widthForStringUsingFontSize($this->getSrc());
        $textHeight = $style->getTextHeight(); // one line
        $x1 = $bBox->getAbsX1() + $padding[3];
        $y1 = $bBox->getAbsY1() - $style->getLineHeight() - $padding[0];
        
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
                $x1 = $bBox->getAbsX2() - $padding[1] - $textWidth;
                break;
        }

        $this->getPdfPage()->drawText(
            trim(strip_tags($this->getSrc())), $x1, $y1, 'UTF-8'
        );

        if(!$this->getStyle()->get(Style::STYLE_HEIGHT)){
            $bBox->setHeight($textHeight - $style->get(Style::STYLE_LINE_SPACING));
        }

    }

}