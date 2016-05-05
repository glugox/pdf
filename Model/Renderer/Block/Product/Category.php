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


class Category extends MultilineText
{
    /**
     * Initializes data needed for rendering
     * of this element.
     */
    public function initialize(\Glugox\PDF\Model\Page\Config $config = null)
    {
        parent::initialize($config);

        $product = $this->getConfig()->getProduct();
        $productCategories = $product->getCategoryCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_path');

        $categoryNames = array();
        foreach ($productCategories as $productCategory) {
            $categoryNames[] = $this->prepareTextForDrawing($productCategory->getName());
        }

        $categoriesBreadcrumb = \implode(" > ", $categoryNames);

        $this->_src = $this->prepareTextForDrawing($categoriesBreadcrumb);
    }


}