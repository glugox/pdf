<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer\Block\Product\Attributes;


use Glugox\PDF\Model\Renderer\Block\MultilineText;

class ListMode extends MultilineText
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
     * Method executed after initializetion
     */
    public function boot()
    {
        parent::boot();
        $product = $this->getConfig()->getProduct();
        if(!$product || !$product instanceof \Magento\Catalog\Model\Product){
            if($this->getParent()){
                $product = $this->getParent()->getSrc();
            }
        }


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

        $txt = '';
        if (\count($data)) {
            $arr = [];
            foreach ($data as $code => $rAttribute) {
                $attributeLabel = $rAttribute['label'];
                $attributeValue = $rAttribute['value'];

                $arr[] = ($attributeLabel . ': ' . $attributeValue);
            }

            $txt = \implode("; ", $arr);
        }

        $this->_src = $this->prepareTextForDrawing($txt);
    }


}