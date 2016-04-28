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


interface RootRendererInterface extends \Glugox\PDF\Model\Renderer\Container\RendererInterface
{


    /**
     * Initializes the zend pdf instance and
     * prepares it for rendering.
     *
     * @return \Glugox\PDF\Model\Renderer\RootRendererInterface
     */
    public function initialize(\Glugox\PDF\Model\Page\Config $config);

}