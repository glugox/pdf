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


interface RendererInterface extends \Glugox\PDF\Model\Renderer\RendererInterface
{

    /**
     * @param \Glugox\PDF\Model\Renderer\RendererInterface $child
     * @return \Glugox\PDF\Model\Renderer\RendererInterface Current renderer
     */
    public function addChild( $child );

    /**
     * @param $childName
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getChild( $childName );

    /**
     * @return array
     */
    public function getChildren();

    /**
     * @return boolean
     */
    public function hasChildren();

}