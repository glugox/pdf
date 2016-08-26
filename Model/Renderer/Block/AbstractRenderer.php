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


use Glugox\PDF\Model\Renderer\Element;

class AbstractRenderer extends Element implements RendererInterface
{


    /**
     * Wether this particular renderer (ex. MultilineText)
     * can request new page or not.
     *
     * @var bool
     */
    protected $_canRequestNewPage = true;


    /**
     * @param $val
     * @return $this
     */
    public function setCanRequestNewPage($val){
        $this->_canRequestNewPage = $val;
        return $this;
    }


    /**
     * @return bool
     */
    public function getCanRequestNewPage(){
        return $this->_canRequestNewPage;
    }
}