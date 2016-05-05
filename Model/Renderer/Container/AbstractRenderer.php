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


use Glugox\PDF\Model\Renderer\Data\Style;
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

        // check if it rendered, no need for layout info
        //  for rendered container.
        if($this->getIsRendered()){
            //return false;
        }

        $occupiedWidth = 0;

        /**
         * Height that occupy rendered intems
         * on the current page
         */
        $occupiedHeight = 0;

        $bBox = $this->getBoundingBox();

        $name = $this->getName();
        $rendName = "";
        $rend = $this->getConfig()->getCurrentRenderingElement();
        if($rend){
            $rendName = $rend->getName();
        }

        if($this->hasChildren()) {

            $prevElement = null;
            $maxOccupiedWidth = 0;
            $lastOccupiedWidth = 0;

            // where to put the current element in the loop
            $currX = 0;
            $currY = 0;

            // where to put the next element in the loop
            $schelduledX = 0;
            $schelduledY = 0;
            $rFloatSchelduledX = $bBox->getInnerWidth();

            $floatersMaxY = 0;

            /** @var \Glugox\PDF\Model\Renderer\RendererInterface $prevElement */
            $prevElement = null;


            /**
             * should be defined for all floating element children
             */
            $floatersMaxHeight = 0;

            /** @var \Glugox\PDF\Model\Renderer\Element $child */
            foreach ($this->getChildren() as $child) {

                /**
                 * TODO: if( !$child->getStyle()->canDisplay() ) continue;
                 */

                $child->updateLayout();

                $childWidth = $child->getBoundingBox()->getOuterWidth();
                $childHeight = $child->getBoundingBox()->getOuterHeight();
                $chIsRendered = $child->getIsRendered();
                $float = $child->getStyle()->get(Style::STYLE_FLOAT);

                switch ($float) {

                    case Style::STYLE_FLOAT_RIGHT:
                    case Style::STYLE_FLOAT_LEFT:

                        if ($prevElement && $prevElement->getStyle()->isFloat()) {
                            // check if there is space to float
                            if ($bBox->canOccupyWidth($childWidth)) {

                                // can float left, prev element was floating
                                $currX = $schelduledX;
                                $currY = $schelduledY;
                                if($float === Style::STYLE_FLOAT_LEFT){
                                    $schelduledX += $childWidth;
                                }else if($float === Style::STYLE_FLOAT_RIGHT){
                                    $currX = $rFloatSchelduledX - $childWidth;
                                    $rFloatSchelduledX = $currX;
                                }
                                $floatersMaxHeight = \max($floatersMaxHeight, $child->getBoundingBox()->getOuterHeight());
                                if($chIsRendered){
                                    $occupiedWidth += $childWidth;
                                    $floatersMaxY = $occupiedHeight - $prevElement->getBoundingBox()->getOuterHeight() + $childHeight;
                                    $occupiedHeight = \max($occupiedHeight, $floatersMaxY);
                                }
                            }else{
                                // reset float, this is first element floating
                                // var floatersMaxHeight has value because
                                // we have previous element floated.
                                $schelduledY += $floatersMaxHeight;
                                $rFloatSchelduledX = $bBox->getInnerWidth();
                                $currY = $schelduledY;
                                if($chIsRendered){
                                    $occupiedWidth = $childWidth;
                                    $occupiedHeight += $childHeight;
                                }
                                if($float === Style::STYLE_FLOAT_LEFT){
                                    $schelduledX = $childWidth;
                                    $currX = 0;
                                }else if($float === Style::STYLE_FLOAT_RIGHT){
                                    $currX = $rFloatSchelduledX - $childWidth;
                                    $rFloatSchelduledX = $currX;
                                }
                                $schelduledY = $currY;
                                $floatersMaxHeight = $childHeight;

                            }
                        }else{
                            
                            $currX = ($float === Style::STYLE_FLOAT_LEFT) ? 0 : ($bBox->getInnerWidth() - $childWidth);
                            $currY = $schelduledY;

                            if($chIsRendered){
                                $occupiedWidth = $childWidth;
                                $occupiedHeight += $childHeight;
                            }
                            $schelduledX = ($float === Style::STYLE_FLOAT_LEFT) ? $childWidth : $schelduledX;
                            $schelduledY = $currY;
                            $floatersMaxHeight = $childHeight;
                            $rFloatSchelduledX = $bBox->getInnerWidth();
                        }
                        break;

                    default:
                        // reset float

                        if($schelduledX > 0 || $rFloatSchelduledX !== $bBox->getInnerWidth()){
                            // last element had float
                            $schelduledX = 0;
                            $schelduledY += $floatersMaxHeight;
                        }
                        $currX = $schelduledX;
                        $currY = $schelduledY;
                        if($chIsRendered){
                            $occupiedWidth = $childWidth;
                            $occupiedHeight += $childHeight;
                        }
                        $schelduledX = 0;
                        $schelduledY += $childHeight;
                        $floatersMaxHeight = 0;
                        $rFloatSchelduledX = $bBox->getInnerWidth();
                }

                $lastOccupiedWidth = $occupiedWidth;
                $maxOccupiedWidth = \max($maxOccupiedWidth, $lastOccupiedWidth);
                // enable this in each item so we can calculate 'canOccupyWidth' properly.
                $bBox->setOccupiedWidth($occupiedWidth);


                $chName = $child->getName();

                //if (!$child->getIsRendered()) {
                    // not rendered - reposition children to previous x,y or 0,0
                    $child->getBoundingBox()->setRelX1($currX);
                    $child->getBoundingBox()->setRelY1($currY);
                //}


                // values set for both rendered and not rendered children
                $prevElement = $child;
            }

            $bBox->setOccupiedHeight($occupiedHeight);
            $bBox->setOccupiedWidth($lastOccupiedWidth);


        }


        parent::updateLayout();
    }


    /**
     * Actions after new pdf page is created
     */
    public function handleNewPage(){

        parent::handleNewPage();
        $this->removeRenderedChildren();
        if($this->hasChildren()){

            /** @var \Glugox\PDF\Model\Renderer\Element $child */
            foreach ($this->getChildren() as $child) {
                $child->handleNewPage();
            }
        }
    }


    /**
     * @return boolean
     */
    public function getIsRendered()
    {
        if($this->hasChildren()){

            /** @var \Glugox\PDF\Model\Renderer\Element $child */
            foreach ($this->getChildren() as $child) {
                if(!$child->getIsRendered()){
                    return false;
                }
            }
        }

        /**
         * If not explicitly set, return true
         */
        return null == parent::getIsRendered() ? true : parent::getIsRendered(); // true for no children!
    }


    /**
     * @return \Zend_Pdf $pdf
     */
    public function render()
    {
        parent::render();

        // mark not rendered yet, in parent::render was set to true
        $this->setIsRendered(false);

        $minY = null;
        $maxY = null;
        if($this->hasChildren()){
            $this->sortChildren();
            foreach ($this->getChildren() as $child) {
                if(!$child->getIsRendered()){

                    $rendered = $child->render();
                    $childMargin = $child->getStyle()->get(Style::STYLE_MARGIN);
                    if($child->getStyle()->canDisplay()){
                        $minY = null === $minY ? $child->getBoundingBox()->getAbsY2() - $childMargin[0] : \min( $minY, $child->getBoundingBox()->getAbsY2() - $childMargin[0] );
                        $maxY = null === $maxY ? $child->getBoundingBox()->getAbsY1() + $childMargin[2] : \max( $maxY, $child->getBoundingBox()->getAbsY1() + $childMargin[2] );
                    }
                    if(Element::NEW_PAGE_FLAG === $rendered){
                        return $rendered;

                    }
                }

            }
        }
        $this->setIsRendered(true);

        if(!$this->getStyle()->get(Style::STYLE_HEIGHT)){
            $height = $maxY - $minY;
            $this->getBoundingBox()->setHeight($height);
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
     * @return \Glugox\PDF\Model\Renderer\RendererInterface[]
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
     * Sorts container children defined by order parameter
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function sortChildren(){
        \usort($this->_children, function($a, $b){
            return $a->getOrder() == $b->getOrder() ? 0 : ( $a->getOrder() < $b->getOrder() ? -1 : 1  );
        });
        return $this;
    }


    /**
     * If we move to another page, we want to get rid of all
     * rendered children (on previous page), so we do not calc
     * layout positions for them any more.
     */
    public function removeRenderedChildren(){
        if($this->hasChildren()){
            /** @var \Glugox\PDF\Model\Renderer\RendererInterface $child */
            foreach ($this->_children as $child) {
                if(  $child->getIsRendered() ){
                    $index = \array_search($child, $this->_children);
                    unset($this->_children[$index]);
                }
            }
        }
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