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


class RendererFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function createContainerRenderer($class = null, array $data = [])
    {

        $class = $class ?: 'Glugox\PDF\Model\Renderer\Container\Renderer';
        return $this->objectManager->create($class, $data);
    }


    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function createBlockRenderer($class = null, array $data = [])
    {

        $class = $class ?: 'Glugox\PDF\Model\Renderer\Block\Renderer';
        return $this->objectManager->create($class, $data);
    }
}
