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

class MultilineText extends AbstractRenderer
{


    /**
     * @var int
     */
    protected $_textHeight = 0;

    /**
     * @var int
     */
    protected $_textWidth = 0;


    /**
     * @var array
     */
    protected $_lines = [];


    /**
     * Total number of lines rendered
     * in this block
     *
     * @var int
     */
    protected $_nLinesRendered = 0;

    /**
     * Number of lines rendered on
     * the current page
     *
     * @var int
     */
    protected $_nLinesRenderedOnPage = 0;

    /**
     * @var array
     */
    protected $_lineColors;



    /**
     * @return int
     */
    public function getMaxLines()
    {
        return $this->getStyle()->get(Style::STYLE_MAX_LINES);
    }


    /**
     * If max lines !== 0
     *
     * @var string
     */
    protected $_truncSuffix = '...';




    /**
     * @return \Zend_Pdf $pdf
     */
    public function _render()
    {
        $bBox = $this->getBoundingBox();
        $style = $this->getStyle();
        $padding = $style->get(Style::STYLE_PADDING);
        $style->applyToPage($this->getPdfPage());

        $maxWidth = $bBox->getInnerWidth();
        $maxHeight = $bBox->getInnerHeight();

        $this->_nLinesRenderedOnPage = 0;
        $truncateFlag = false;
        $maxLines = $this->getMaxLines();

        if($maxWidth > 0){

            $line = "";
            $lineHeight = $style->getLineHeight(); // one line
            $width = 0;

            $src = $this->getSrc();
            if(!empty($src) && empty($this->_lines)){

                //$textArr = \explode(" ", $this->getSrc());
                $textArr = \preg_split('/\s/si', $this->getSrc());

                $n = \count($textArr);
                for( $k=0; $k < $n; ++$k ){
                    $word = $textArr[$k] . ' ';
                    $currWidth = $style->widthForStringUsingFontSize($word);
                    $width += $currWidth;
                    /**
                     * while we are in allowed width,
                     * or if the word as single is larger than max width
                     */
                    if($width <= $maxWidth || ($currWidth === $width && $currWidth > $maxWidth)){
                        $line .= $word;
                    }else{

                        $line = \trim($line);
                        if($maxLines && \count($this->_lines) >= ($maxLines-1) && $k < ($n-1)){ // if this is last line allowed, but would have more text
                            $truncateFlag = true;
                            $line = \substr($line, 0, \strlen($line) - \strlen($this->_truncSuffix)) . $this->_truncSuffix;
                        }
                        /**
                         * store the line, move index back
                         * as the last word did not fit in this loop
                         */
                        $this->_lines[] = $line;

                        $width -= $currWidth;
                        $this->_textWidth = \max($this->_textWidth, $width);
                        $line = "";
                        $width = 0;
                        --$k;
                    }
                    if($truncateFlag){
                        break;
                    }
                }
                if(!empty($line)){
                    $this->_lines[] = \trim($line);
                    $width -= $currWidth;
                    $this->_textWidth = \max($this->_textWidth, $width);
                }
            }

        }

        $this->_textHeight = $lineHeight * \count($this->_lines);
        $x1 = $bBox->getAbsX1() + $padding[3];

        // - 1 x getTextHeight means - because text is drawn from bottom of the text
        $y1 = $bBox->getAbsY1() - $style->getTextHeight() - $padding[0];
        
        $align = $style->get(Style::STYLE_TEXT_ALIGN);

        switch ($align[0]){
            case Style::ALIGN_TOP:
                // aleady set
                break;
            case Style::ALIGN_BOTTOM:
                $y1 = $bBox->getAbsY2() + $padding[2] + $this->_textHeight - $lineHeight;
                break;
        }

        switch ($align[1]){
            case Style::ALIGN_LEFT:
                // aleady set
                break;
            case Style::ALIGN_RIGHT:
                $x1 = $bBox->getAbsX2() - $padding[1] - $this->_textWidth;
                break;
        }


        // new page?
        if(false !== $this->checkNewPage($y1)){
            return Element::NEW_PAGE_FLAG;
        }

        if(\count($this->_lines)){
            $index = 0;
            $changedColor = false;
            foreach ($this->_lines as $line) {
                if($index < $this->_nLinesRendered){
                    ++$index;
                    continue;
                }
                $linePrepared = $this->prepareTextForDrawing($line);
                if(isset($this->_lineColors[$index]['color'])){
                    $this->getPdfPage()->setFillColor(new \Zend_Pdf_Color_Html($this->_lineColors[$index]['color']));
                    $changedColor = true;
                    if(isset($this->_lineColors[$index]['line-through'])){
                        $estimatedWidth = $this->getStyle()->widthForStringUsingFontSize($linePrepared);
                        $this->getPdfPage()->setLineColor(new \Zend_Pdf_Color_Html($this->_lineColors[$index]['color']));
                        $this->getPdfPage()->drawLine($x1, $y1 + 0.3 * $lineHeight, $x1 + $estimatedWidth, $y1 + 0.3 * $lineHeight);
                    }
                }
                $this->getPdfPage()->drawText(
                    $linePrepared, $x1, $y1, 'UTF-8'
                );
                ++$this->_nLinesRendered;
                ++$this->_nLinesRenderedOnPage;
                ++$index;
                if($changedColor){
                    $this->getStyle()->applyToPage($this->getPdfPage());
                    $changedColor = false;
                }
                $y1 -= $lineHeight;
                if(false !== $this->checkNewPage($y1)){
                    return Element::NEW_PAGE_FLAG;
                }

            }
        }

        $this->_textHeight = $lineHeight * $this->_nLinesRenderedOnPage;

        if(!$this->getStyle()->get(Style::STYLE_HEIGHT)){
            $bBox->setHeight($this->_textHeight - $style->get(Style::STYLE_LINE_SPACING));
        }


    }

    /**
     * @return void
     */
    protected function checkNewPage( $y ){

        if( $y < $this->getBottomMostY() ){
            return Element::NEW_PAGE_FLAG;
        }
        return false;
    }

}