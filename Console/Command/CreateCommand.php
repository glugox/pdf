<?php

/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glugox\PDF\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\StringInput;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class CreateCommand
 */
class CreateCommand extends Command {

    /**
     * Current command output interface code.
     */
    const CURRENT_CMD_OUTPUT_INTERFACE = 'current_cmd_output_interface';

    /**
     * Code argument
     */
    const SKU_ARGUMENT = 'sku';

    /**
     * Create pdf of all products code
     */
    const OPTION_CREATE_ALL = 'create-all';

    /**
     * Create pdf of all products in one or more category
     */
    const OPTION_CREATE_CATEGORIES = 'create-categories';

    /**
     * Create pdf of all products filtered by attributes
     */
    const OPTION_FILTER = 'filter';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    /**
     *
     * @var \Glugox\PDF\Model\PDFService
     */
    protected $_pdfService;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    //protected $_varDirectory;

    /** @var \Glugox\PDF\Helper\Data */
    protected $_helper;


    /**
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry,
            \Glugox\PDF\Model\PDFService $pdfService,
            \Magento\Framework\Filesystem $filesystem,
            \Glugox\PDF\Helper\Data $helper) {

        $this->_registry = $registry;
        $this->_pdfService = $pdfService;
        $this->_pdfService->setAreaCode("frontend");
        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_helper = $helper;
        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('glugox:pdf:create')
                ->setDescription('Creates pdf of one or more products.')
                ->setDefinition($this->_pdfService->getCreateCommandDefinition());
        parent::configure();
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {


        $sku = $input->getArgument(self::SKU_ARGUMENT);
        $all = $input->getOption(self::OPTION_CREATE_ALL);


        $output->writeln('<info> Creating PDF - ' . (!$all ? ('sku:' . $sku) : ('all')) . '  ...</info>');

        // Register the console output to the global registry, so it can be used from other parts to display console info too.
        $this->_registry->register(self::CURRENT_CMD_OUTPUT_INTERFACE, $output);



        /** @var \Glugox\PDF\Model\PDFResult * */
        $pdfResult = $this->_pdfService->serve((string) $input, $this->getDefinition());
        $pdf = $pdfResult->getPdf();
        $filename = $pdfResult->getFileneme();

        //$output->writeln('<info> Saving PDF to : ' . $filename . '  ...</info>');

        if($pdf){
            $pdf->save($filename);
        }

    }


}
