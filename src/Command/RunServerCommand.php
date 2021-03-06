<?php

namespace Blackjack\Command;

use Blackjack\Server\Server;
use Blackjack\Server\TableManager;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunServerCommand extends Command
{
    private $container;

    protected function configure()
    {
        $this->setName('run-server');
        $this->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Server port', 8000);
        $this->addOption('websocket-port', null, InputOption::VALUE_REQUIRED, 'Websocket port', 8001);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var TableManager $tableManager */
        $tableManager = $this->container['table_manager'];

        /** @var WsServer $webSocketServer */
        $webSocketServer = $this->container['websocket_server'];

        /** @var Server $socketServer */
        $socketServer = $this->container['socket_server'];

        /** @var LoopInterface $loop */
        $loop = $this->container['loop'];

        $tableManager->createTable();

        $socket = new \React\Socket\Server($loop);
        $socket->listen($input->getOption('websocket-port'), '0.0.0.0');
        new IoServer(new HttpServer($webSocketServer), $socket, $loop);

        $socketServer->listen($input->getOption('port'));
        $loop->run();
    }

    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }
}
