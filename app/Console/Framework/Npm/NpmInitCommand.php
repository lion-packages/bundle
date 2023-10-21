<?php

namespace App\Console\Framework\Npm;

use App\Traits\Framework\ClassPath;
use App\Traits\Framework\ConsoleOutput;
use LionFiles\Store;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NpmInitCommand extends Command
{
	use ConsoleOutput, ClassPath;

	protected static $defaultName = "npm:init";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this
            ->setDescription("Command to create Javascript projects with Vite.JS (Vanilla/Vue/React/Preact/Lit/Svelte/Solid/Qwik)")
            ->addArgument('project', InputArgument::OPTIONAL, 'Project name', "example")
            ->addOption('template', 't', InputOption::VALUE_OPTIONAL, 'Do you want a template? (Vanilla/Vue/React/Preact/Lit/Svelte/Solid/Qwik)', 'react');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
		$project = str->of($input->getArgument("project"))->trim()->replace("_", "-")->replace(" ", "-")->get();

        // check if the resource exists before generating it
        if (isSuccess(Store::exist("vite/{$project}/"))) {
            $output->writeln($this->errorOutput("\t>>  VITE: a resource with this name already exists"));
            return Command::FAILURE;
        }

        $vite = kernel->getViteProjects();
        $conf = [];

        $tmp = $input->getOption('template');
        $cmd = kernel->execute("cd vite/ && echo | npm init vite@latest {$project} -- --template {$tmp}", false);
        $output->writeln(arr->of($cmd)->join("\n"));
        kernel->execute("cd vite/{$project}/ && npm install", false);
        $this->new("vite/{$project}/", "env");
        $this->force();
        $this->close();

        $vite['app'][$project] = [
            'type' => 'vite',
            'path' => "{$project}/"
        ];

        $conf = [
            "[program:resource-{$project}]",
            "command=npm run dev",
            "directory=/var/www/html/vite/{$project}",
            'autostart=true',
            'autorestart=true',
            'redirect_stderr=true',
            "stdout_logfile=/var/www/html/storage/logs/vite/{$project}.log"
        ];

        $cont_ports = 0;

        foreach ([...$vite['framework'], ...$vite['app']] as $key => $resource) {
            if ($resource['type'] === 'vite' && $key != $project) {
                $cont_ports++;
            }
        }

        $port = (5173 + $cont_ports);

        $replace = [
            'replace' => true,
            'content' => ",\n  server: {\n    host: true,\n    port: {$port},\n    watch: {\n      usePolling: true\n    }\n  }",
            'search' => ","
        ];

        // expose port
        $docker_compose = Store::get("docker-compose.yml");
        file_put_contents("docker-compose.yml",
            str->of($docker_compose)
                ->replace('"8000:8000"', '"8000:8000"' . "\n            " . '- "' . $port . ":" . $port . '"')
                ->get()
        );

        if (isSuccess(Store::exist("vite/{$project}/vite.config.js"))) {
            $this->readFileRows("vite/{$project}/vite.config.js", [6 => $replace]);
        }

        if (isSuccess(Store::exist("vite/{$project}/vite.config.ts"))) {
            $this->readFileRows("vite/{$project}/vite.config.ts", [6 => $replace]);
        }

        // add logger
        $this->new("storage/logs/vite/{$project}", "log");
        $this->force();
        $this->close();

        // add resources
        file_put_contents("config/vite.php",
            str->of("<?php")->ln()->ln()
                ->concat("/**")->ln()
                ->concat(" * ------------------------------------------------------------------------------")->ln()
                ->concat(" * Resources for developing your web application")->ln()
                ->concat(" * ------------------------------------------------------------------------------")->ln()
                ->concat(" * List of available resources")->ln()
                ->concat(" * ------------------------------------------------------------------------------")->ln()
                ->concat(" **/")->ln()->ln()
                ->concat("return")
                ->concat(var_export($vite, true))
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
        $supervisord = Store::get("supervisord.conf");

        if (stripos($supervisord, "program:resource-{$project}") !== true) {
            file_put_contents("supervisord.conf",
                str->of($supervisord)
                    ->replace("; resources", "; resources\n\n" . arr->of($conf)->join("\n"))
                    ->get()
            );
        }

        $output->writeln($this->warningOutput("\t>>  VITE: {$project}"));
        $output->writeln($this->successOutput("\t>>  VITE: the '{$project}/' resource has been generated"));

		return Command::SUCCESS;
	}
}
