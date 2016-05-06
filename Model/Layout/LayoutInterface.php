<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Layout;


use Glugox\PDF\Model\Page\Config;

interface LayoutInterface
{


    /**
     * @return Config
     */
    public function getConfig();

    /**
     * @param Config $config
     */
    public function setConfig($config);

    /**
     * Create renderers using parent-child relations and
     * add them as tree into root renderer.
     *
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function getRootRenderer();

}