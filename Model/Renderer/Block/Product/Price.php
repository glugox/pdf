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
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;


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

        $this->_lines[] = $regularPriceFormatted;

        /**
         * Discounted price
         */
        $priceInfoFinal = $this->getConfig()->getHelper()->getProductPrice($product, 'final_price');
        if ($priceInfoFinal) {
            $finalPrice = $priceInfoFinal->getValue();
            if ($finalPrice && $finalPrice < $regularPrice) {
                $finalPriceFormatted = $this->_priceCurrency->format($finalPrice, false);
                $this->_lines[] = $finalPriceFormatted;
                $this->_textWidth = \max($this->_textWidth, $style->widthForStringUsingFontSize($finalPriceFormatted));
                $this->_textHeight += $lineHeight;

                $discountedColor = "#cccccc";
                $this->_lineColors = [
                    0 => ["color"=>$discountedColor, "line-through"=>true]
                ];
            }
        }


        $this->_src = '';
    }


}