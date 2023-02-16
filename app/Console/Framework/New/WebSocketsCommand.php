<?php

namespace App\Console\Framework\New;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Traits\Framework\ClassPath;
use LionFiles\Store;

class WebSocketsCommand extends Command {

	protected static $defaultName = "new:socket";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Creating WebSocket...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
        $this->setDescription(
            'Command required for creating new WebSockets'
        )->addArgument(
            'websocket', InputArgument::REQUIRED, '', null
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$list = ClassPath::export("app/Http/Sockets/", $input->getArgument('websocket'));
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        ClassPath::create($url_folder, $list['class']);
        ClassPath::add("<?php\n\n");
        ClassPath::add("namespace {$list['namespace']};\n\n");
        ClassPath::add("use Ratchet\\ConnectionInterface;\n");
        ClassPath::add("use Ratchet\\MessageComponentInterface;\n");
        ClassPath::add("use \SplObjectStorage;\n\n");
        ClassPath::add("class {$list['class']} implements MessageComponentInterface {\n\n");
        ClassPath::add("\t" . 'protected SplObjectStorage $clients;' . "\n\n");
        ClassPath::add("\tpublic function __construct() {\n");
        ClassPath::add("\t\t" . '$this->clients = new SplObjectStorage();' . "\n");
        ClassPath::add("\t}\n\n");
        ClassPath::add("\t" . 'public function onOpen(ConnectionInterface $conn) {' . "\n");
        ClassPath::add("\t\t" . '$this->clients->attach($conn);' . "\n");
        ClassPath::add("\t}\n\n");
        ClassPath::add("\t" . 'public function onMessage(ConnectionInterface $from, $msg) {' . "\n");
        ClassPath::add("\t\t" . 'foreach ($this->clients as $client) {' . "\n");
        ClassPath::add("\t\t\t" . 'if ($from !== $client) {' . "\n");
        ClassPath::add("\t\t\t\t" . '$client->send($msg);' . "\n");
        ClassPath::add("\t\t\t}\n\t\t}\n");
        ClassPath::add("\t}\n\n");
        ClassPath::add("\t" . 'public function onClose(ConnectionInterface $conn) {' . "\n");
        ClassPath::add("\t\t" . '$this->clients->detach($conn);' . "\n");
        ClassPath::add("\t}\n\n");
        ClassPath::add("\t" . 'public function onError(ConnectionInterface $conn, \Exception $e) {' . "\n");
        ClassPath::add("\t\t" . '$conn->close();' . "\n");
        ClassPath::add("\t}\n\n");
        ClassPath::add("}");
        ClassPath::force();
        ClassPath::close();

        $output->writeln("<info>WebSocket created successfully</info>");
        return Command::SUCCESS;
	}

}