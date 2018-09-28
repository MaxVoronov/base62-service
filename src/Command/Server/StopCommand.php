<?php declare(strict_types=1);

namespace App\Command\Server;

use App\Daemon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends Command
{
    /** @var Daemon */
    protected $daemon;

    /**
     * StopCommand constructor
     *
     * @param Daemon $daemon
     */
    public function __construct(Daemon $daemon)
    {
        $this->daemon = $daemon;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('stop')
            ->setDescription('Stop server');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Stopping server... ');
        $this->daemon->stop();
        $output->writeln('Done!');
    }
}
