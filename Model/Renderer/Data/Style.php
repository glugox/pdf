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
use Magento\TestFramework\Inspection\Exception;

class Style
{


    /**
     * Supported style types
     */
    const STYLE_WIDTH          = 'width';
    const STYLE_HEIGHT         = 'height';
    const STYLE_TOP            = 'top';
    const STYLE_LEFT           = 'left';
    const STYLE_BG_COLOR       = 'background-color';
    const STYLE_MARGIN_TOP     = 'margin-top';
    const STYLE_MARGIN_RIGHT   = 'margin-right';
    const STYLE_MARGIN_BOTTOM  = 'margin-bottom';
    const STYLE_MARGIN_LEFT    = 'margin-left';
    const STYLE_MARGIN         = 'margin';
    const STYLE_PADDING_TOP    = 'padding-top';
    const STYLE_PADDING_RIGHT  = 'padding-right';
    const STYLE_PADDING_BOTTOM = 'padding-bottom';
    const STYLE_PADDING_LEFT   = 'padding-left';
    const STYLE_PADDING        = 'padding';
    const STYLE_POSITION       = 'position';
    const STYLE_COLOR          = 'color';

    const SUPPORDED_TYPES = [

        self::STYLE_WIDTH,          self::STYLE_HEIGHT,         self::STYLE_BG_COLOR,
        self::STYLE_TOP,            self::STYLE_LEFT,           self::STYLE_MARGIN_TOP,
        self::STYLE_MARGIN_RIGHT,   self::STYLE_MARGIN_BOTTOM,  self::STYLE_MARGIN_LEFT,
        self::STYLE_MARGIN,         self::STYLE_PADDING_TOP,    self::STYLE_PADDING_RIGHT,
        self::STYLE_PADDING_BOTTOM, self::STYLE_PADDING_LEFT,   self::STYLE_PADDING,
        self::STYLE_POSITION,       self::STYLE_COLOR
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
    public function __construct( $source, \Glugox\PDF\Model\Renderer\RendererInterface $element )
    {
        $this->_source = $source;
        $this->_element = $element;
        $this->parseSource();
        $this->inheritFromParent();
    }


    /**
     * @param $source
     * @return Style
     */
    public static function getInstance( $source, \Glugox\PDF\Model\Renderer\RendererInterface $element ){
        if(!is_string($source)){
            throw new PDFException( __("Style source must be string!") );
        }
        return new self( $source,$element );
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
        $val = isset($this->_data[$styleKey]) ? $this->_data[$styleKey] : $default;
        switch ($styleKey){
            case self::STYLE_MARGIN:
                return [$this->get(self::STYLE_MARGIN_TOP, 0), $this->get(self::STYLE_MARGIN_RIGHT, 0), $this->get(self::STYLE_MARGIN_BOTTOM, 0), $this->get(self::STYLE_MARGIN_LEFT, 0)];
                break;
            case self::STYLE_PADDING:
                return [$this->get(self::STYLE_PADDING_TOP, 0), $this->get(self::STYLE_PADDING_RIGHT, 0), $this->get(self::STYLE_PADDING_BOTTOM, 0), $this->get(self::STYLE_PADDING_LEFT, 0)];
                break;
        }
        return $val;
    }

    /**
     * @param string $styleKey
     * @return mixed
     */
    public function set( $styleKey, $styleValue, $override = true ){

        if(\in_array( $styleKey, self::SUPPORDED_TYPES )){

            if(isset($this->_data[$styleKey]) && !$override){
                return false;
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
                    $styleValue = (double) $styleValue;
                    break;
                case self::STYLE_BG_COLOR:
                    $styleValue = (string) $styleValue;
                    break;
                case self::STYLE_MARGIN:
                    list( $top, $right, $bottom, $left ) = $this->_process04Value($styleValue);

                    // set the values without override
                    $this->set(self::STYLE_MARGIN_TOP, $top, false);
                    $this->set(self::STYLE_MARGIN_RIGHT, $right, false);
                    $this->set(self::STYLE_MARGIN_BOTTOM, $bottom, false);
                    $this->set(self::STYLE_MARGIN_LEFT, $left, false);
                    break;
                case self::STYLE_PADDING:
                    list( $top, $right, $bottom, $left ) = $this->_process04Value($styleValue);

                    // set the values without override
                    $this->set(self::STYLE_PADDING_TOP, $top, false);
                    $this->set(self::STYLE_PADDING_RIGHT, $right, false);
                    $this->set(self::STYLE_PADDING_BOTTOM, $bottom, false);
                    $this->set(self::STYLE_PADDING_LEFT, $left, false);
                    break;

            }

            $this->_data[$styleKey] = $styleValue;
        }
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

}