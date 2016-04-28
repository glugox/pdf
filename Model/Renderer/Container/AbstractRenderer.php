<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer\Container;


use Glugox\PDF\Model\Renderer\Element;

class AbstractRenderer extends Element implements RendererInterface
{

    /**
     * @var array Children elements insode this container
     */
    protected $_children = [];


    /**
     * Initializes data needed for rendering
     * of this element.
     */
    public function initialize( \Glugox\PDF\Model\Page\Config $config = null ){
        
        
        parent::initialize($config);
        if($this->hasChildren()){
            foreach ($this->getChildren() as $child) {
                $child->initialize($config);
            }
        }
    }

    /**
     * Updates children properties needed for rendering
     * like bounding box (x,y,width,height) after page state
     * is changed (like rendered new element)
     */
    public function updateLayout(){
        parent::updateLayout();
        if($this->hasChildren()){
            foreach ($this->getChildren() as $child) {
                $child->updateLayout();
            }
        }
    }


    /**
     * @return \Zend_Pdf $pdf
     */
    public function render()
    {
        parent::render();
        if($this->hasChildren()){

            \usort($this->_children, function($a, $b){
                return $a->getOrder() == $b->getOrder() ? 0 : ( $a->getOrder() < $b->getOrder() ? -1 : 1  );
            });

            foreach ($this->getChildren() as $child) {
                $child->render();
            }
        }

        return $this->getPdf();
    }


    /**
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $child
     * @return \Glugox\PDF\Model\Renderer\RendererInterface Current renderer
     */
    public function addChild( $child ){
        $this->_children[] = $child;
        $child->setParent($this);
    }

    /**
     * @param $childName
     * @return \Glugox\PDF\Model\Renderer\RendererInterface|null
     */
    public function getChild( $childName ){
        foreach ($this->getChildren() as $child) {
            if($child->getName() === $childName ){
                return $child;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getChildren(){
        return $this->_children;
    }

    /**
     * @return boolean
     */
    public function hasChildren(){
        return !empty($this->_children);
    }


    /**
     * @return array
     */
    public function getShortInfo()
    {
        $data = parent::getShortInfo();
        if($this->hasChildren()){
            $data["children"] = [];
            foreach ($this->getChildren() as $child) {
                $data["children"][] = $child->getShortInfo();
            }
        }
        return $data;
    }

}