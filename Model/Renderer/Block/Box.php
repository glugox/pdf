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

class Box extends AbstractRenderer
{
    /**
     * Overriding render method
     */
    public function _render()
    {
        $this->getStyle()->applyToPage($this->getPdfPage());
        $bBox = $this->getBoundingBox();

        // draw
        $this->getPdfPage()->drawRectangle(
            $bBox->getAbsX1(),
            $bBox->getAbsY1(),
            $bBox->getAbsX2(),
            $bBox->getAbsY2(),
            \Zend_Pdf_Page::SHAPE_DRAW_FILL
        );
    }


}