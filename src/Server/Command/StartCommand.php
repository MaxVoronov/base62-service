<?php declare(strict_types=1);

namespace App\Server\Command;

use App\Config\Config;
use App\Server\Daemon;
use App\Server\Daemon\Runnable\RunnableInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    /** @var \App\Server\Daemon */
    protected $daemon;

    /** @var \App\Server\Daemon\Runnable\RunnableInterface */
    protected $runnable;

    /** @var \App\Config\Config */
    protected $config;

    /**
     * StartCommand constructor
     *
     * @param Daemon $daemon
     * @param RunnableInterface $runnable
     * @param Config $config
     */
    public function __construct(Daemon $daemon, RunnableInterface $runnable, Config $config)
    {
        $this->daemon = $daemon;
        $this->config = $config;
        $this->runnable = $runnable;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('start')
            ->setDescription('Start server')
            ->addOption(
                'no-daemonize',
                'f',
                InputOption::VALUE_NONE,
                'Start in foreground mode'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf(
            'Server listening %s:%s',
            $this->config->getHost(),
            $this->config->getPort()
        ));

        $noDaemonMode = (bool) $input->getOption('no-daemonize');
        $this->daemon->start($this->runnable, !$noDaemonMode);
    }
}
