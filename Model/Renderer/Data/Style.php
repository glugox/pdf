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
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Inspection\Exception;


class Style
{


    /**
     * Supported style types
     */
    const STYLE_WIDTH                  = 'width';
    const STYLE_HEIGHT                 = 'height';
    const STYLE_TOP                    = 'top';
    const STYLE_LEFT                   = 'left';
    const STYLE_BG_COLOR               = 'background-color';
    const STYLE_MARGIN_TOP             = 'margin-top';
    const STYLE_MARGIN_RIGHT           = 'margin-right';
    const STYLE_MARGIN_BOTTOM          = 'margin-bottom';
    const STYLE_MARGIN_LEFT            = 'margin-left';
    const STYLE_MARGIN                 = 'margin';
    const STYLE_PADDING_TOP            = 'padding-top';
    const STYLE_PADDING_RIGHT          = 'padding-right';
    const STYLE_PADDING_BOTTOM         = 'padding-bottom';
    const STYLE_PADDING_LEFT           = 'padding-left';
    const STYLE_PADDING                = 'padding';
    const STYLE_POSITION               = 'position';
    const STYLE_COLOR                  = 'color';
    const STYLE_FLOAT                  = 'float';
    const STYLE_FLOAT_LEFT             = 'left';
    const STYLE_FLOAT_RIGHT            = 'right';
    const STYLE_FONT                   = 'font';
    const STYLE_FONT_BOLD              = 'font-bold';
    const STYLE_FONT_SIZE              = 'font-size';
    const STYLE_TEXT_ALIGN             = 'text-align';
    const STYLE_TEXT_VERTICAL_ALIGN    = 'vertical-align';
    const STYLE_TEXT_HORIZONTAL_ALIGN  = 'horizontal-align';
    const STYLE_LINE_SPACING           = 'line-spacing';
    const STYLE_DISPLAY                = 'display';
    const STYLE_FONT_WEIGHT            = 'font-weight';

    // specific
    const STYLE_COLOR_PRICE_OLD        = 'color-price-old';
    const STYLE_MAX_LINES              = 'max-lines';


    const SUPPORDED_TYPES = [

        self::STYLE_WIDTH,          self::STYLE_HEIGHT,         self::STYLE_BG_COLOR,
        self::STYLE_TOP,            self::STYLE_LEFT,           self::STYLE_MARGIN_TOP,
        self::STYLE_MARGIN_RIGHT,   self::STYLE_MARGIN_BOTTOM,  self::STYLE_MARGIN_LEFT,
        self::STYLE_MARGIN,         self::STYLE_PADDING_TOP,    self::STYLE_PADDING_RIGHT,
        self::STYLE_PADDING_BOTTOM, self::STYLE_PADDING_LEFT,   self::STYLE_PADDING,
        self::STYLE_POSITION,       self::STYLE_COLOR,          self::STYLE_FLOAT,
        self::STYLE_FONT_SIZE,      self::STYLE_TEXT_ALIGN,     self::STYLE_TEXT_VERTICAL_ALIGN,
        self::STYLE_TEXT_HORIZONTAL_ALIGN, self::STYLE_LINE_SPACING, self::STYLE_FONT,
        self::STYLE_DISPLAY,        self::STYLE_FONT_WEIGHT,   self::STYLE_COLOR_PRICE_OLD,
        self::STYLE_MAX_LINES
    ];


    /**
     * If parent container has set ex color value, and the child container has not set color value,
     * it should inherit the value from its parent element
     */
    const INHERITED_VALUES = [

        self::STYLE_BG_COLOR,
        self::STYLE_POSITION,
        self::STYLE_COLOR
    ];


    const STYLE_DEFAULTS = [
        self::STYLE_FONT_SIZE => 12,
        self::STYLE_LINE_SPACING => 10 /* (0.3 * 12)*/,
        self::STYLE_FONT => 'lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf',
        self::STYLE_FONT_BOLD => 'lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf',
        self::STYLE_COLOR => "#000000",
        self::STYLE_BG_COLOR => "#FFFFFF",
        self::STYLE_DISPLAY => 'block',
        self::STYLE_COLOR_PRICE_OLD => '#cccccc',
        self::STYLE_MAX_LINES => 0
    ];


    const SIZE_AUTO     = 'auto';
    const ALIGN_TOP     = 'top';
    const ALIGN_RIGHT   = 'right';
    const ALIGN_BOTTOM  = 'bottom';
    const ALIGN_LEFT    = 'left';
    const ALIGN_CENTER  = 'center';

    /**
     * For default rendering
     */
    const STYLE_COLOR_GRAY = '#cccccc';

    /**
     * Processed style data
     *
     * @var array
     */
    protected $_data = [];


    /**
     * Html like css style
     *
     * @var string
     */
    protected $_source = '';


    /**
     * Rendering element, owner of this style element
     *
     * @var \Glugox\PDF\Model\Renderer\RendererInterface
     */
    protected $_element;


    /**
     * @var \Zend_Pdf_Font[]
     */
    protected $_fontResources;


    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_rootDirectory;


    /**
     * @return string
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->_source = $source;
    }


    /**
     * Style constructor.
     * @param string $source
     */
    public function __construct(
        $source,
        \Glugox\PDF\Model\Renderer\RendererInterface $element,
        \Magento\Framework\Filesystem $filesystem )
    {
        $this->_source = $source;
        $this->_element = $element;
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->parseSource();
        $this->inheritFromParent();
    }



    /**
     * Parses html like css
     */
    public function parseSource(){

        if(empty($this->_source)){
            return false;
        }

        $source = \trim($this->_source);

        // array of key values strings
        $aKeyVals = \explode(";", $source);

        foreach ($aKeyVals as $sKeyVal) {

            // key value string
            $item = \trim($sKeyVal);

            // key value array
            if( \strpos($sKeyVal, ":") > 0 ){
                list( $key, $value ) = \explode(":", $sKeyVal);
                $key = \trim($key);
                $value = \trim($value);
                $this->set($key, $value);
            }
        }
    }


    /**
     * Inherites defined values from the parent element's style
     */
    protected function inheritFromParent(){

        if($this->_element->hasParent()) {
            $parentStyle = $this->_element->getParent()->getStyle();
            if($parentStyle){
                foreach (self::INHERITED_VALUES as $key) {
                    if (!isset($this->_data[$key])) {
                        $parentValue = $parentStyle->get($key);
                        if(!empty($parentValue)){
                            $this->set($key, $parentValue);
                        }

                    }
                }
            }
        }
    }

    /**
     * @param string $styleKey
     * @return mixed
     */
    public function get( $styleKey, $default = null ){

        if(null === $default && \array_key_exists($styleKey, self::STYLE_DEFAULTS)){
            $default = self::STYLE_DEFAULTS[$styleKey];
            if($styleKey === self::STYLE_FONT && 'bold' === $this->get(self::STYLE_FONT_WEIGHT)){
                $default = self::STYLE_DEFAULTS[self::STYLE_FONT_BOLD];
            }
        }
        
        $method = 'get'. \strtoupper(\str_replace("-", "", $styleKey));
        if(\method_exists($this, $method)){
            $val = \call_user_func_array([$this, $method], []);
        }else{
            $val = isset($this->_data[$styleKey]) ? $this->_data[$styleKey] : $default;
        }
        
        
        switch ($styleKey){
            case self::STYLE_MARGIN:
                return [$this->get(self::STYLE_MARGIN_TOP, 0), $this->get(self::STYLE_MARGIN_RIGHT, 0), $this->get(self::STYLE_MARGIN_BOTTOM, 0), $this->get(self::STYLE_MARGIN_LEFT, 0)];
                break;
            case self::STYLE_PADDING:
                return [$this->get(self::STYLE_PADDING_TOP, 0), $this->get(self::STYLE_PADDING_RIGHT, 0), $this->get(self::STYLE_PADDING_BOTTOM, 0), $this->get(self::STYLE_PADDING_LEFT, 0)];
                break;
            case self::STYLE_TEXT_ALIGN:
                return [$this->get(self::STYLE_TEXT_VERTICAL_ALIGN, self::ALIGN_TOP), $this->get(self::STYLE_TEXT_HORIZONTAL_ALIGN, self::ALIGN_LEFT)];
                break;
        }
        return $val;
    }


    /**
     * @return bool
     */
    public function isFloatLeft(){
        return $this->get(self::STYLE_FLOAT) === self::STYLE_FLOAT_LEFT;
    }


    /**
     * @return bool
     */
    public function isFloatRight(){
        return $this->get(self::STYLE_FLOAT) === self::STYLE_FLOAT_RIGHT;
    }


    /**
     * @return bool
     */
    public function isFloat(){
        return $this->isFloatLeft() || $this->isFloatRight();
    }


    /**
     * @return bool
     */
    public function canDisplay(){
        return $this->get(self::STYLE_DISPLAY) !== 'none';
    }


    /**
     * @param string $styleKey
     * @return Style
     */
    public function set( $styleKey, $styleValue, $override = true ){

        if(\in_array( $styleKey, self::SUPPORDED_TYPES )){

            if(isset($this->_data[$styleKey]) && !$override){
                return $this;
            }

            switch ($styleKey){
                case self::STYLE_WIDTH:
                case self::STYLE_HEIGHT:
                case self::STYLE_TOP:
                case self::STYLE_LEFT:
                case self::STYLE_MARGIN_TOP:
                case self::STYLE_MARGIN_RIGHT:
                case self::STYLE_MARGIN_BOTTOM:
                case self::STYLE_MARGIN_LEFT:
                case self::STYLE_PADDING_TOP:
                case self::STYLE_PADDING_RIGHT:
                case self::STYLE_PADDING_BOTTOM:
                case self::STYLE_PADDING_LEFT:
                case self::STYLE_LINE_SPACING:
                    $styleValue = (double) $styleValue;
                    break;
                case self::STYLE_BG_COLOR:
                    $styleValue = (string) $styleValue;
                    break;
                case self::STYLE_MARGIN:
                    list( $top, $right, $bottom, $left ) = $this->_process04Value($styleValue);

                    // set the values without override
                    $this->set(self::STYLE_MARGIN_TOP, $top, true);
                    $this->set(self::STYLE_MARGIN_RIGHT, $right, true);
                    $this->set(self::STYLE_MARGIN_BOTTOM, $bottom, true);
                    $this->set(self::STYLE_MARGIN_LEFT, $left, true);
                    break;
                case self::STYLE_PADDING:
                    list( $top, $right, $bottom, $left ) = $this->_process04Value($styleValue);

                    // set the values without override
                    $this->set(self::STYLE_PADDING_TOP, $top, true);
                    $this->set(self::STYLE_PADDING_RIGHT, $right, true);
                    $this->set(self::STYLE_PADDING_BOTTOM, $bottom, true);
                    $this->set(self::STYLE_PADDING_LEFT, $left, true);
                    break;
                case self::STYLE_TEXT_ALIGN:
                    list( $verticalAlign, $horizontalAlign ) = $this->_processAlignValue($styleValue);

                    $this->set(self::STYLE_TEXT_VERTICAL_ALIGN, $verticalAlign, true);
                    $this->set(self::STYLE_TEXT_HORIZONTAL_ALIGN, $horizontalAlign, true);
                    break;
                case self::STYLE_FLOAT:
                    if(!\in_array($styleValue, ["left", "right"])){
                        $styleValue = null;
                    }
                    break;

            }

            $this->_data[$styleKey] = $styleValue;
        }

        return $this;
    }


    /**
     * @param \Zend_Pdf_Page $page
     * @return Style
     */
    public function applyToPage( \Zend_Pdf_Page $page ){

        $page->setFont($this->getFontResource(), $this->get(self::STYLE_FONT_SIZE));
        $page->setAlpha(1);
        $page->setFillColor(new \Zend_Pdf_Color_Html($this->get(self::STYLE_COLOR)));

        return $this;
    }


    /**
     * @return \Zend_Pdf_Resource_Font
     * @throws \Zend_Pdf_Exception
     */
    public function getFontResource(){

        $fontName = $this->get(self::STYLE_FONT);
        if(!isset($this->_fontResources[$fontName])){
            try {
                $this->_fontResources[$fontName] = \Zend_Pdf_Font::fontWithName($fontName);
            } catch (\Zend_Pdf_Exception $ex) {
                //
            }
            if (!isset($this->_fontResources[$fontName])) {
                try {
                    $path = $this->_rootDirectory->getAbsolutePath($fontName);
                    $this->_fontResources[$fontName] = \Zend_Pdf_Font::fontWithPath($path);
                } catch (\Zend_Pdf_Exception $ex) {
                    //
                }
            }
        }
        return $this->_fontResources[$fontName];
    }


    /**
     * @return float
     */
    public function getTextHeight(){
        return 0.7 * $this->get(Style::STYLE_FONT_SIZE);
    }


    /**
     * @return float
     */
    public function getLineHeight(){
        return $this->getTextHeight() + $this->get(self::STYLE_LINE_SPACING);
    }



    /**
     * @param $value
     * @return array
     */
    protected function _process04Value($value){
        if( empty($value) ){
            return [0,0,0,0];
        }
        $a = \explode(" ", $value);
        switch (\count($a)){
            case 1:
                $m = (double)$value;
                return [$m, $m, $m, $m];
                break;
            case 2:
                $v = (double)$a[0];
                $h = (double)$a[1];
                return [$v, $h, $v, $h];
                break;
            case 3:
                $t = (double)$a[0];
                $h = (double)$a[1];
                $b = (double)$a[2];
                return [$t, $h, $b, $h];
                break;
            case 4:
                return [(double)$a[0], (double)$a[1], (double)$a[2], (double)$a[3]];
                break;
        }
    }


    /**
     * @param $value
     * @return array
     */
    protected function _processAlignValue($value){
        if( empty($value) ){
            return [self::ALIGN_TOP, self::ALIGN_LEFT];
        }
        $allowed = [self::ALIGN_TOP, self::ALIGN_RIGHT, self::ALIGN_BOTTOM, self::ALIGN_LEFT];
        $a = \explode(" ", $value);
        switch (\count($a)){
            case 1:
                $m = (string)$value;
                if(in_array($m, $allowed)){
                    return [$m, $m];
                }else{
                    return [self::ALIGN_TOP, self::ALIGN_LEFT];
                }
                break;
            case 2:
                $v = !in_array((string)$a[0], $allowed) ? self::ALIGN_TOP : (string)$a[0];
                $h = !in_array((string)$a[1], $allowed) ? self::ALIGN_LEFT : (string)$a[1];
                return [$v, $h];
                break;
        }
    }

    /**
     * @return bool
     */
    public function isWidthAuto(){
        return $this->get(self::STYLE_WIDTH) === self::SIZE_AUTO;
    }


    /**
     * @return bool
     */
    public function isHeightAuto(){
        return $this->get(self::STYLE_HEIGHT) === self::SIZE_AUTO;
    }


    /**
     * Calculate the width of a string:
     * in case of using alignment parameter in drawText
     * @param string $text
     * @param Zend_Pdf_Font $font
     * @param float $fontSize
     * @return float
     */
    public function widthForStringUsingFontSize($text, $font=null, $fontSize=null)
    {
        if(!$font){
            $font = $this->getFontResource();
        }
        if(!$fontSize){
            $fontSize = $this->get(self::STYLE_FONT_SIZE);
        }
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $text);
        $characters    = array();
        for ($i = 0; $i < strlen($drawingString); $i ++) {
            $characters[] = (ord($drawingString[$i ++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }

}