<?php declare(strict_types=1);

namespace App\Client\Command;

use App\Service\Base62Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EncodeCommand extends Command
{
    /** @var Base62Service */
    protected $base62Service;

    public function __construct(Base62Service $base62Service)
    {
        $this->base62Service = $base62Service;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('encode')
            ->setDescription('Encode any string to base62')
            ->addArgument('payload', InputArgument::REQUIRED, 'String for encoding');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->base62Service->encode($input->getArgument('payload'));
        $output->writeln('Result: ' . $result);
    }
}
