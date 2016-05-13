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


use Glugox\PDF\Model\Page\Config;
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
     * @var \Glugox\PDF\Model\Page\Config
     */
    protected $_config;


    /**
     * Cache constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Glugox\PDF\Model\Page\Config $config)
    {
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_fileFactory = $fileFactory;
        $this->_config = $config;
    }


    /**
     * @return Page\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }


    /**
     * @param $filename
     * @return bool
     */
    public function has( $filename ){
        if($this->getConfig()->getData(Config::CACHE_ENABLED)){
            return $this->_rootDirectory->isFile($filename);
        }
        return false;
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