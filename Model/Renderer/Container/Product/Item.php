<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer\Container\Product;


use Glugox\PDF\Model\Renderer\Data\Style;


class Item extends \Glugox\PDF\Model\Renderer\Container\AbstractRenderer
{

    /**
     * It actually sets the size of the block to estimated size!
     * As this is repeating item, we will suppose we don't need the
     * size estimation for the first item in the parent repeater.
     * Than every next repeating item will get the previous item size
     * as the estimated size.
     *
     * @return array
     */
    public function _estimateSize(){
        if( null === $this->_estimatedSize ){
            $this->_estimatedSize = [0,0];
        }
        if( $this->getParent() && $this->getParent()->getLastRenderedItem() ){
            $this->_estimatedSize = [
                $this->getParent()->getLastRenderedItem()->getBoundingBox()->getWidth(),
                $this->getParent()->getLastRenderedItem()->getBoundingBox()->getHeight()
            ];
        }
        if(!$this->getStyle()->get(Style::STYLE_HEIGHT)){
            $this->getBoundingBox()->setHeight($this->_estimatedSize[1]);
        }

        return $this->_estimatedSize;
    }

    
    /**
     * @return \Zend_Pdf $pdf
     */
    public function _render()
    {
        //
    }

}