<?php
/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Model;


use Magento\Framework\App\Filesystem\DirectoryList;

class Cache
{

    /**
     * Storage directory relative
     * to VAR directory.
     */
    const STORAGE_DIR = 'pdf';

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;


    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;


    /**
     * Cache constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, \Magento\Framework\App\Response\Http\FileFactory $fileFactory)
    {
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_fileFactory = $fileFactory;
    }


    /**
     * @param $filename
     * @return bool
     */
    public function has( $filename ){
        return $this->_rootDirectory->isFile($filename);
    }


    /**
     * @param filename
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function getResult($filename, $file){
        return $this->_fileFactory->create($filename , ["type" => "filename", "value" => $file], DirectoryList::ROOT, 'application/pdf');
    }


    /**
     * @param $filename
     * @return bool
     */
    public function delete( $filename ){

        if($this->has($filename)){
            $fullPath = $this->get($filename);
            if($fullPath){
                return \unlink($fullPath);
            }
        }
        return false;
    }

    
    /**
     * @param $filename
     * @return null|string
     */
    public function get( $filename ){
        $fullPath = $filename;
        if(!$this->_rootDirectory->isExist($fullPath)){
            return null;
        }
        return $this->_rootDirectory->getAbsolutePath($fullPath);

    }

}