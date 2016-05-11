<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Renderer;


interface RendererInterface
{



    /**
     * Updates properties needed for rendering
     * like bounding box (x,y,width,height) after page state
     * is changed (like rendered new element)
     */
    public function updateLayout();


    /**
     * Initializes the zend pdf instance and
     * prepares it for rendering.
     *
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function initialize(\Glugox\PDF\Model\Page\Config $config=null);

    /**
     * Method executed after initializetion
     */
    public function boot();

    /**
     * @param string $name
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setName($name);

    /**
     * @return string Name
     */
    public function getName();

    /**
     * Parent renderer element setter
     *
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $name
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setParent($renderer);

    /**
     * Parent renderer element getter
     *
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getParent();

    /**
     * Only root should not have parent renderer
     *
     * @return boolean
     */
    public function hasParent();

    /**
     * Config setter
     *
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $name
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setConfig(\Glugox\PDF\Model\Page\Config $config);

    /**
     * Config getter
     *
     * @return \Glugox\PDF\Model\Page\Config
     */
    public function getConfig();

    /**
     * @return \Glugox\PDF\Model\Renderer\Data\Style
     */
    public function getStyle();

    /**
     * @param $style
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setStyle($style);

    /**
     * @return \Zend_Pdf $pdf
     */
    public function render();

    /**
     * @return \Glugox\PDF\Model\Renderer\Data\BoundingBox
     */
    public function getBoundingBox();

    /**
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setBoundingBox(\Glugox\PDF\Model\Renderer\Data\BoundingBox $boundingBox);

    /**
     * Returns true if this element is block type element.
     *
     * @return boolean
     */
    public function isBlock();


    /**
     * Returns true if this element is container type element.
     *
     * @return boolean
     */
    public function isContainer();

    /**
     * @return boolean
     */
    public function getIsRendered();

    /**
     * @param boolean $isRendered
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setIsRendered($isRendered);

    /**
     * @return mixed
     */
    public function getSrc();

    /**
     * @param string $src
     */
    public function setSrc($src);

    /**
     * @return int
     */
    public function getOrder();

    /**
     * @param int $order
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function setOrder($order);

}