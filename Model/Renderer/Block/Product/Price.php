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


use Glugox\PDF\Model\Renderer\Block\MultilineText;

class Price extends MultilineText
{


    /**
     * If there is a discount, regular price is
     * on the first and discounted price on the
     * second line
     */
    const MODE_2_LINES = 'mode_2_lines';
    /**
     * If there is a discount, both regular price
     * and discounted prices are in one line.
     */
    const MODE_1_LINE = 'mode_1_line';

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var string
     */
    protected $_mode = self::MODE_2_LINES;

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
    }



    /**
     * Price constructor.
     * @param string $type
     * @param int $order
     * @param string $style
     * @param string $src
     */
    public function __construct($type, $order, $style, $src, \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency)
    {
        parent::__construct($type, $order, $style, $src);
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * Initializes data needed for rendering
     * of this element.
     */
    public function initialize(\Glugox\PDF\Model\Page\Config $config = null)
    {
        parent::initialize($config);

        $product = $this->getConfig()->getProduct();
        if(!$product || !$product instanceof \Magento\Catalog\Model\Product){
            if($this->getParent()){
                $product = $this->getParent()->getSrc();
            }
        }

        $style = $this->getStyle();
        $lineHeight = $style->getLineHeight();

        /**
         * Regular price
         */
        $priceInfoRegular = $this->getConfig()->getHelper()->getProductPrice($product, 'regular_price');
        $regularPrice = $priceInfoRegular->getValue();
        $regularPriceFormatted = $this->_priceCurrency->format($regularPrice, false);
        $this->_textWidth = $style->widthForStringUsingFontSize($regularPriceFormatted);
        $this->_textHeight = $lineHeight;

        $this->_lines[0] = $regularPriceFormatted;

        /**
         * Discounted price
         */
        $priceInfoFinal = $this->getConfig()->getHelper()->getProductPrice($product, 'final_price');
        if ($priceInfoFinal) {
            $finalPrice = $priceInfoFinal->getValue();
            if ($finalPrice && $finalPrice < $regularPrice) {
                $finalPriceFormatted = $this->_priceCurrency->format($finalPrice, false);
                if($this->_mode === self::MODE_2_LINES){
                    $this->_lines[1] = $finalPriceFormatted;
                    $this->_textWidth = \max($this->_textWidth, $style->widthForStringUsingFontSize($finalPriceFormatted));
                    $this->_textHeight += $lineHeight;
                }else{
                    $this->_lines[0] = $this->_lines[0] . '&nbspc;&nbspc;' . $finalPriceFormatted;
                    $this->_textWidth = $style->widthForStringUsingFontSize($this->_lines[0]);
                }


                $discountedColor = "#cccccc";
                $this->_lineColors = [
                    0 => ["color"=>$discountedColor, "line-through"=>true]
                ];
            }
        }


        $this->_src = '';
    }


}