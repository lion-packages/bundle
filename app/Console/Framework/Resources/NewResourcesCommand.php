<?php

namespace App\Console\Framework\Resources;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \ZipArchive;

class NewResourcesCommand extends Command {

    use ClassPath, ConsoleOutput;

    protected static $defaultName = "resource:new";

    protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("Command required to generate a resource")
            ->addArgument('resource', InputArgument::OPTIONAL, 'Resource name', "example")
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Do you want to create a different resource? (vite/twig)', 'vite')
            ->addOption('template', 'm', InputOption::VALUE_OPTIONAL, 'Do you want a template? (Vanilla/Vue/React/Preact/Lit/Svelte/Solid/Qwik)', 'react');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $rsc = str->of($input->getArgument("resource"))->trim()->replace("_", "-")->replace(" ", "-")->get();
        $type = $input->getOption('type');

        // check if the resource exists before generating it
        if (isSuccess(Store::exist("resources/{$rsc}/"))) {
            $output->writeln($this->errorOutput("\t>>  RESOURCE: a resource with this name already exists"));
            return Command::FAILURE;
        }

        $resources = kernel->getResources();
        $supervisord = Store::get("supervisord.conf");
        $conf = [];
        $info = [];

        if ($type === "vite") {
            $tmp = $input->getOption('template');
            $cmd = kernel->execute("cd resources/ && npm init vite@latest {$rsc} -- --template {$tmp}", false);
            $output->writeln(arr->of($cmd)->join("\n"));
            kernel->execute("cd resources/{$rsc}/ && npm install", false);

            $resources['app'][$rsc] = [
                'type' => 'vite',
                'path' => "{$rsc}/"
            ];

            $conf = [
                "[program:resource-{$rsc}]",
                "command=npm run dev",
                "directory=/var/www/html/resources/{$rsc}",
                'autostart=true',
                'autorestart=true',
                'redirect_stderr=true',
                "stdout_logfile=/var/www/html/storage/logs/resources/{$rsc}.log"
            ];
        } elseif ($type === 'twig') {
            // unzip template in zip format
            $zip = new ZipArchive();
            $zip->open(storage_path("framework/templates/twig/example.zip", false));
            $zip->extractTo("resources/");
            $zip->close();

            // rename resource folder
            $output->writeln($this->warningOutput("\t>>  RESOURCE: {$rsc}"));

            if (is_dir("resources/example/")) {
                if (!rename("resources/example/", "resources/{$rsc}/")) {
                    $output->writeln($this->errorOutput("\t>>  RESOURCE: failed to generate resource"));
                    return Command::FAILURE;
                }
            }

            $resources['app'][$rsc] = [
                'type' => 'twig',
                'host' => '0.0.0.0',
                'port' => 7000,
                'path' => "{$rsc}/"
            ];

            $conf = [
                "[program:resource-{$rsc}]",
                "command=php lion resource:serve {$rsc}",
                "directory=/var/www/html",
                'autostart=true',
                'autorestart=true',
                'redirect_stderr=true',
                "stdout_logfile=/var/www/html/storage/logs/resources/{$rsc}.log"
            ];
        } else {
            $output->writeln($this->errorOutput("\t>>  RESOURCE: The requested resource does not exist"));
            return Command::INVALID;
        }

        // add logger
        $this->new("storage/logs/resources/{$rsc}", "log");
        $this->force();
        $this->close();

        // add resources
        file_put_contents("config/resources.php",
            str->of("<?php")->ln()->ln()
            ->concat("/**")->ln()
            ->concat(" * ------------------------------------------------------------------------------")->ln()
            ->concat(" * Resources for developing your web application")->ln()
            ->concat(" * ------------------------------------------------------------------------------")->ln()
            ->concat(" * List of available resources")->ln()
            ->concat(" * ------------------------------------------------------------------------------")->ln()
            ->concat(" **/")->ln()->ln()
            ->concat("return")
            ->concat(var_export($resources, true))
            ->concat(";")
            ->replace("array", "")
            ->replace("(", "[")
            ->replace(")", "]")
            ->replace("=> \n   [", "=> [")
            ->replace("=> \n     [", "=> [")
            ->replace("  '", "    '")
            ->replace("      '", "        '")
            ->replace("  ],", "    ],")
            ->replace("      ],", "        ],")
            ->replace("          '", "            '")
            ->get()
        );

        // add supervisord
        file_put_contents("supervisord.conf",
            str->of($supervisord)
            ->replace("; resources", "; resources\n" . arr->of($conf)->join("\n") . "\n")
            ->get()
        );

        $output->writeln($this->warningOutput("\t>>  RESOURCE: {$rsc}"));
        $output->writeln($this->successOutput("\t>>  RESOURCE: the '{$rsc}/' resource has been generated"));
        return Command::FAILURE;
    }

}
