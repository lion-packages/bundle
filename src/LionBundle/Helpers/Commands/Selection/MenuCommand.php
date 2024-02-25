<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Selection;

use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Files\Store;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class MenuCommand extends Command
{
    protected Arr $arr;
    protected Store $store;
    protected Str $str;

    /**
     * @required
     * */
    public function setArr(Arr $arr): MenuCommand
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * @required
     * */
    public function setStore(Store $store): MenuCommand
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): MenuCommand
    {
        $this->str = $str;

        return $this;
    }

    protected function selectedProject(InputInterface $input, OutputInterface $output): string
    {
        $projects = [];

        foreach ($this->store->view('./vite/') as $folder) {
            if (is_dir($folder) && $folder != '.' && $folder != '..') {
                $split = $this->str->of($folder)->split($this->store->normalizePath('vite/'));
                $projects[] = end($split);
            }
        }

        if (count($projects) <= 1) {
            $output->writeln($this->warningOutput('(default: ' . reset($projects) . ')'));

            return reset($projects);
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                ('Select project ' . $this->warningOutput('(default: ' . reset($projects) . ')')),
                $projects,
                0
            )
        );
    }

    protected function selectedTemplate(InputInterface $input, OutputInterface $output, array $templates): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $helper->ask(
            $input,
            $output,
            new ChoiceQuestion("Select the type of template {$this->warningOutput('(default: React)')}", $templates, 2)
        );
    }

    protected function selectedTypes(InputInterface $input, OutputInterface $output, array $types): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $helper->ask(
            $input,
            $output,
            new ChoiceQuestion("Select type {$this->warningOutput('(default: js)')}", $types, 0)
        );
    }

    protected function selectConnection(InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $connections = DB::getConnections();
        $selectedConnection = null;

        if ($this->arr->of($connections['connections'])->length() > 1) {
            $selectedConnection = $helper->ask(
                $input,
                $output,
                new ChoiceQuestion(
                    'Select a connection ' . $this->warningOutput("(default: {$connections['default']})"),
                    $this->arr->of($connections['connections'])->keys()->get(),
                    0
                )
            );
        } else {
            $output->writeln($this->warningOutput("default connection: ({$connections['default']})"));
            $selectedConnection = $connections['default'];
        }

        $_ENV['SELECTED_CONNECTION'] = $selectedConnection;

        return $selectedConnection;
    }

    protected function selectConnectionByEnviroment(InputInterface $input, OutputInterface $output): string
    {
        if (empty($_ENV['SELECTED_CONNECTION'])) {
            return $this->selectConnection($input, $output);
        } else {
            return $_ENV['SELECTED_CONNECTION'];
        }
    }

    protected function selectMigrationType(InputInterface $input, OutputInterface $output, $options): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $helper->ask(
            $input,
            $output,
            new ChoiceQuestion("Select the type of migration {$this->warningOutput('(default: Table)')}", $options, 0)
        );
    }
}
