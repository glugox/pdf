<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model\Page;


class State
{

    /**
     * Page referenced state
     */

    /**
     * Current drawing x position
     *
     * @var int
     */
    protected $_x;

    /**
     * Current drawing y position
     *
     * @var int
     */
    protected $_y;

    /**
     * @return int
     */
    public function getX()
    {
        return $this->_x;
    }

    /**
     * @param int $x
     * @return \Glugox\PDF\Model\Page\State
     */
    public function setX($x)
    {
        $this->_x = $x;
        return $this;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->_y;
    }

    /**
     * @param int $y
     * @return \Glugox\PDF\Model\Page\State
     */
    public function setY($y)
    {
        $this->_y = $y;
        return $this;
    }



}