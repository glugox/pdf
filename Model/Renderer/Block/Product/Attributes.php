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

class Attributes extends AbstractRenderer
{


    /**
     * @var array
     */
    protected $_estimatedSize;

    /**
     * @return array
     */
    public function _estimateSize(){
        if( null === $this->_estimatedSize ){
            $this->_estimatedSize = [0,0];
            $product = $this->getConfig()->getProduct();
            $attributes = $product->getAttributes();
            $nAttrs = 0;
            foreach ($attributes as $attribute) {
                if ($attribute->getIsVisibleOnFront()){
                    $value = $attribute->getFrontend()->getValue($product);
                    if (is_string($value) && strlen($value)) {
                        ++$nAttrs;
                    }
                }
            }
            $this->_estimatedSize[1] = $this->getStyle()->getLineHeight() * $nAttrs;
        }

        if(!$this->getStyle()->get(Style::STYLE_HEIGHT)){
            $this->getBoundingBox()->setHeight($this->_estimatedSize[1]);
        }

        return $this->_estimatedSize;
    }


    /**
     * Rendering attributes.
     */
    public function _render()
    {

        $product = $this->getConfig()->getProduct();
        $attributes = $product->getAttributes();
        $style = $this->getStyle();
        $data = [];

        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = __('N/A');
                } elseif ((string) $value == '') {
                    $value = __('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen($value)) {
                    $data[$attribute->getAttributeCode()] = [
                        'label' => __($attribute->getStoreLabel()),
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                    ];
                }
            }
        }

        if (\count($data)) {

            /**
             * Draw attribute labels
             */
            $bBox = $this->getBoundingBox();
            $lineHeight = $this->getStyle()->getLineHeight();
            $y = $bBox->getAbsY1();
            $x = $bBox->getAbsX1();
            // draw attributes labels
            $attrLabelsMaxW = 0;
            $this->getStyle()->set(Style::STYLE_FONT_WEIGHT, 'bold')->applyToPage($this->getPdfPage());
            foreach ($data as $code => $rAttribute) {

                $attributeLine = $rAttribute['label'];
                $attributeLine = $this->prepareTextForDrawing($attributeLine);
                $estimatedWidth = $this->getStyle()->widthForStringUsingFontSize($attributeLine);
                $attrLabelsMaxW = \max($estimatedWidth, $attrLabelsMaxW);

                $y -= $lineHeight;
                $this->getPdfPage()->drawText(
                    \trim(\strip_tags($attributeLine)), $x, $y, 'UTF-8'
                );
            }

            /**
             * Draw attribute values
             */
            $x += ($attrLabelsMaxW + 20);
            $y = $bBox->getAbsY1();
            $attrValuesMaxW = 0;
            $this->getStyle()->set(Style::STYLE_FONT_WEIGHT, 'normal')->applyToPage($this->getPdfPage());
            foreach ($data as $code => $rAttribute) {

                $attributeLine = $rAttribute['value'];
                $attributeLine = $this->prepareTextForDrawing($attributeLine);
                $estimatedWidth = $this->getStyle()->widthForStringUsingFontSize($attributeLine);
                $attrValuesMaxW = \max($estimatedWidth, $attrValuesMaxW);

                $y -= $lineHeight;
                $this->getPdfPage()->drawText(
                    trim(strip_tags($attributeLine)), $x, $y, 'UTF-8'
                );
            }
        }
    }


}