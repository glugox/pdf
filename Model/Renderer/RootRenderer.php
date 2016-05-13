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


use Glugox\PDF\Exception\PDFException;

class RootRenderer extends \Glugox\PDF\Model\Renderer\Container\AbstractRenderer implements RendererInterface
{
    /**
     * Initializes the zend pdf instance and
     * prepares it for rendering.
     *
     * @return \Glugox\PDF\Model\Renderer\RendererInterface
     */
    public function initialize(\Glugox\PDF\Model\Page\Config $config = null)
    {

        if( null === $config ){
            throw new PDFException(__("Initialize method for root renderer must have config set!"));
        }

        $this->setConfig($config);
        parent::initialize();
        
        $this->_pdf = new \Zend_Pdf;




        return $this;
    }

    /**
     * Method executed after initializetion
     */
    public function boot()
    {
        $this->getConfig()->processConfigStyling();
        $this->inheritStyling();
        $this->getConfig()->newPage();
        parent::boot();
    }


    


    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->_underscore(substr($method, 3));
                $index = isset($args[0]) ? $args[0] : null;
                return $this->getConfig()->getData($key, $index);
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            new \Magento\Framework\Phrase('Invalid method %1::%2(%3)', [get_class($this), $method, print_r($args, 1)])
        );
    }


}