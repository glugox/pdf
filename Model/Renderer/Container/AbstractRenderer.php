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
     * @var \Glugox\PDF\Model\Renderer\RendererInterface
     */
    protected $_lastRenderedItem = null;

    /**
     * @var \Glugox\PDF\Model\Renderer\RendererInterface
     */
    protected $_currentRenderingItem = null;


    protected $_highestRenderedItem = null;


    /**
     * @var int Number of rendered children of the container.
     */
    //protected $_numRenderedChildren;

    /**
     * Initializes data needed for rendering
     * of this element.
     */
    public function initialize( \Glugox\PDF\Model\Page\Config $config = null ){

        //$this->getConfig()->log("Container '{$this->getName()}' initialize()");
        //$this->_numRenderedChildren = 0;
        parent::initialize($config);
        if($this->hasChildren()){
            foreach ($this->getChildren() as $child) {
                $child->initialize($config);
            }
        }
    }

    /**
     * Method executed after initializetion
     */
    public function boot(){
        parent::boot();
        if($this->hasChildren()){
            foreach ($this->getChildren() as $child) {
                $child->boot();
            }
        }
    }


    /**
     * Loop styling children , and if some default values are not
     * set for the child, inherit that vlaue from parent.
     */
    public function inheritStyling(){
        $this->getStyle()->inheritFromParent();
        if($this->hasChildren()){
            foreach ($this->getChildren() as $child) {
                $child->inheritStyling();
            }
        }
    }

    /**
     * Updates children properties needed for rendering
     * like bounding box (x,y,width,height) after page state
     * is changed (like rendered new element)
     */
    public function updateLayout(){


        //$this->getConfig()->log("Container '{$this->getName()}' updateLayout()");

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
                                $schelduledX = 0;
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
        //$this->removeRenderedChildren();
        if($this->hasChildren()){

            /** @var \Glugox\PDF\Model\Renderer\Element $child */
            foreach ($this->getChildren() as $child) {
                $child->handleNewPage();
            }
        }
    }

    /**
     * @param bool $isRendered
     * @param bool $recursively
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setIsRendered($isRendered, $recursively = false)
    {
        if($recursively && $this->hasChildren()){
            foreach ($this->getChildren() as $child) {
                $child->setIsRendered($isRendered, $recursively);
            }
        }
        return parent::setIsRendered($isRendered, $recursively);
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


        $parentRenderResult = parent::render();
        // mark not rendered yet, in parent::render was set to true
        $this->setIsRendered(false);
        $this->setIsRendering(true);

        if(Element::NEW_PAGE_FLAG === $parentRenderResult){
            return $parentRenderResult;
        }

        $minY = null;
        $maxY = null;
        if($this->hasChildren()){
            $this->sortChildren();
            foreach ($this->getChildren() as $child) {
                if(!$child->getIsRendered()){

                    $this->_currentRenderingItem = $child;
                    $rendered = $child->render();
                    $this->_lastRenderedItem = $child;

                    $childMargin = $child->getStyle()->get(Style::STYLE_MARGIN);
                    if($child->getStyle()->canDisplay()){
                        $childMinY = $child->getBoundingBox()->getAbsY2() - $childMargin[0];
                        $childMaxY = $child->getBoundingBox()->getAbsY1() + $childMargin[2];
                        $minY = null === $minY ? $childMinY : \min( $minY, $childMinY );
                        $maxY = null === $maxY ? $childMaxY : \max( $maxY, $childMaxY );

                        if(!$this->_highestRenderedItem || ($childMaxY - $childMinY) > $this->_highestRenderedItem->getBoundingBox()->getOuterHeight()){
                            $this->_highestRenderedItem = $child;
                        }
                    }
                    $childName = $child->getName();
                    if(Element::NEW_PAGE_FLAG === $rendered){
                        $this->_currentRenderingItem = null;
                        return $rendered;
                    }
                    //++$this->_numRenderedChildren;
                }

            }
            $this->_currentRenderingItem = null;

        }
        $this->setIsRendered(true);
        $this->setIsRendering(false);
        /*if($this->getParent()){
            $this->getParent()->setCurrentRenderedItem(null);
        }*/

        if(!$this->getStyle()->get(Style::STYLE_HEIGHT)){
            $height = $maxY - $minY;
            $this->getBoundingBox()->setHeight($height);
        }


        //$this->getConfig()->log("Rendered : " . $this->getName());
        $this->getConfig()->handleContainerRendered($this);

        return $this->getPdf();
    }


    /**
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getLastRenderedItem(){
        return $this->_lastRenderedItem;
    }


    /**
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getCurrentRenderedItem(){
        return $this->_currentRenderingItem;
    }

    /**
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setCurrentRenderedItem(\Glugox\PDF\Model\Renderer\RendererInterface $item=null){
        $this->_currentRenderingItem = $item;
    }

    /**
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getHighestRenderedItem(){
        return $this->_highestRenderedItem;
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
        if(\strpos($childName, "/") > 0){
            $path = \explode("/", $childName);
            $element = $this;
            foreach ($path as $part){
                $element = $element->getChild($part);
                if(!$element){
                    return null;
                }
            }
            return $element;
        }else{
            foreach ($this->getChildren() as $child) {
                if($child->getName() === $childName ){
                    return $child;
                }
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
     * @param \Glugox\PDF\Model\Renderer\RendererInterface[]
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setChildren($children){
        $this->_children = $children;
        foreach ($children as $child) {
            $child->setParent($this);
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasChildren(){
        return !empty($this->_children) && \count($this->_children) > 0;
    }


    /**
     * @return int
     */
    public function getNumChildren(){
        return \count($this->_children);
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
     * @return int
     */
    public function getNumRenderedChildren(){
        $num = 0;
        if($this->hasChildren()){
            /** @var \Glugox\PDF\Model\Renderer\RendererInterface $child */
            foreach ($this->_children as $child) {
                if(  $child->getIsRendered() ){
                    ++$num;
                }
            }
        }
        return $num;
    }

    /**
     * @return int
     */
    public function getProgress()
    {
        $progress = 0;
        $dbg = ['name' => $this->getName(), 'numRenderedChildren' => 0, 'numChildren' => 0, 'currRenderedItem' => null];
        if($this->hasChildren()){
            $dbg['numRenderedChildren'] = $this->getNumRenderedChildren();
            $dbg['numChildren'] = $this->getNumChildren();
            $dbg['childrenNames'] = [];
            foreach ($this->getChildren() as $child) {
                $dbg['childrenNames'][] = $child->getName();
            }
            $progress = $this->getNumRenderedChildren() / $this->getNumChildren() * 100;
            //if($progress > 100){
                //$this->getConfig()->log( "Container: " . $this->getName() . ", progress: " . $progress . " ({$this->getNumRenderedChildren()}/{$this->getNumChildren()})" );
            //}

            if($this->getCurrentRenderedItem() && !$this->getCurrentRenderedItem()->getIsRendered() && $progress < 100){


                $childProgress = $this->getCurrentRenderedItem()->getProgress();
                $dbg['currRenderedItem'] = ['name' => $this->getCurrentRenderedItem()->getName(), 'progress' => $childProgress];

                $numChildren = $this->getNumChildren();
                $dProgress = 1/$numChildren * $childProgress['value'];
                $dbg['dProgress'] = $dProgress;
                $progress += $dProgress;

            }
        }else{
            $progress = parent::getProgress();

        }


        return ['value'=>$progress, 'dbg' => $dbg];
    }





    /**
     * When an object is cloned, PHP 5 will perform a shallow copy of all of the object's properties.
     * Any properties that are references to other variables, will remain references.
     * Once the cloning is complete, if a __clone() method is defined,
     * then the newly created object's __clone() method will be called, to allow any necessary properties that need to be changed.
     * NOT CALLABLE DIRECTLY.
     *
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.cloning.php
     */
    function __clone()
    {


        if(null === $this->_style){
            // clone the style by creating new one from source
            $this->setStyle($this->getStyle()->getSource());
        }else{
            // clone the style directly, it may have been modified since source is parsed.
            $cStyle = clone $this->_style;
            $this->setStyle($cStyle);
        }


        // mark the bounding box ready for creating new one.
        $this->_boundingBox = null;

        $clonedChildren = [];
        if($this->hasChildren()){
            foreach ($this->getChildren() as $child) {
                $clonedChildren[] = clone $child;
            }
            $this->_children = [];
            foreach ($clonedChildren as $cChild) {
                $this->addChild($cChild);
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