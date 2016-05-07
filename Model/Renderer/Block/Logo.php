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


use Glugox\PDF\Model\Renderer\Data\Style;

class Logo extends Image
{

    /** @var \Glugox\PDF\Helper\Data */
    protected $_helper;

    /**
     * Logo constructor.
     * @param string $type
     * @param int $order
     * @param string $style
     */
    public function __construct($type, $order, $style, \Glugox\PDF\Helper\Data $helper )
    {
        parent::__construct($type, $order, $style);
        $this->_helper = $helper;
        $imagePath = $this->_helper->getLogoImagePath();
        $this->setSrc( $imagePath );
    }

    /**
     * Initializes data needed for rendering
     * of this element.
     */
    public function initialize(\Glugox\PDF\Model\Page\Config $config = null)
    {
        parent::initialize($config);
    }


}