<?php

namespace App\Console\Framework\Sockets;

use App\Traits\Framework\ClassPath;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewSocketCommand extends Command {

    use ClassPath;

	protected static $defaultName = "socket:new";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
        $this
            ->setDescription('Command required for creating new WebSockets')
            ->addArgument('socket', InputArgument::OPTIONAL, 'Socket name', "ExampleSocket");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $socket = $input->getArgument('socket');
		$list = $this->export("app/Http/Sockets/", $socket);
        $url_folder = lcfirst(str_replace("\\", "/", $list['namespace']));
        Store::folder($url_folder);

        $this->create($url_folder, $list['class']);
        $this->add("<?php\n\n");
        $this->add("namespace {$list['namespace']};\n\n");
        $this->add("use Ratchet\\ConnectionInterface;\n");
        $this->add("use Ratchet\\MessageComponentInterface;\n");
        $this->add("use \SplObjectStorage;\n\n");
        $this->add("class {$list['class']} implements MessageComponentInterface {\n\n");
        $this->add("\t" . 'protected int $port = 8080;' . "\n");
        $this->add("\t" . 'protected SplObjectStorage $clients;' . "\n\n");
        $this->add("\tpublic function __construct() {\n");
        $this->add("\t\t" . '$this->clients = new SplObjectStorage();' . "\n");
        $this->add("\t}\n\n");
        $this->add("\tpublic function getPort(): int {\n");
        $this->add("\t\t" . 'return $this->port;' . "\n");
        $this->add("\t}\n\n");
        $this->add("\t" . 'public function onOpen(ConnectionInterface $conn) {' . "\n");
        $this->add("\t\t" . 'echo("New connection! ({$conn->resourceId})\n");' . "\n");
        $this->add("\t\t" . '$this->clients->attach($conn);' . "\n");
        $this->add("\t}\n\n");
        $this->add("\t" . 'public function onMessage(ConnectionInterface $from, $msg) {' . "\n");
        $this->add("\t\t" . 'foreach ($this->clients as $client) {' . "\n");
        $this->add("\t\t\t" . 'if ($from !== $client) {' . "\n");
        $this->add("\t\t\t\t" . '$client->send($msg);' . "\n");
        $this->add("\t\t\t}\n\t\t}\n");
        $this->add("\t}\n\n");
        $this->add("\t" . 'public function onClose(ConnectionInterface $conn) {' . "\n");
        $this->add("\t\t" . '$this->clients->detach($conn);' . "\n");
        $this->add("\t}\n\n");
        $this->add("\t" . 'public function onError(ConnectionInterface $conn, \Exception $e) {' . "\n");
        $this->add("\t\t" . '$conn->close();' . "\n");
        $this->add("\t}\n\n");
        $this->add("}");
        $this->force();
        $this->close();

        $output->writeln("<comment>\t>>  SOCKET: {$socket}</comment>");
        $output->writeln("<info>\t>>  SOCKET: The '{$list['namespace']}\\{$list['class']}' socket has been generated</info>");

        return Command::SUCCESS;
	}

}
