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

/**
 * Command class for selecting different types of selection menu
 *
 * @property Arr $arr [Arr class object]
 * @property Store $store [Store class object]
 * @property Str $str [Str class object]
 */
class MenuCommand extends Command
{
    /**
     * [Arr class object]
     *
     * @var Arr $arr
     */
    protected Arr $arr;

    /**
     * [Store class object]
     *
     * @var Store $store
     */
    protected Store $store;

    /**
     * [Str class object]
     *
     * @var Str $str
     */
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

    /**
     * Selection menu to obtain a Vite.JS project
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return string
     */
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

    /**
     * Open a menu to select a template to create a project with vite
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param array $templates [List of available templates]
     * @param string $defaultTemplate [Default template]
     * @param int $defaultIndex [Default index]
     *
     * @return string
     */
    protected function selectedTemplate(
        InputInterface $input,
        OutputInterface $output,
        array $templates,
        string $defaultTemplate = 'React',
        int $defaultIndex = 0
    ): string {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $choiceQuestion = new ChoiceQuestion(
            "Select the type of template {$this->warningOutput("(default: {$defaultTemplate})")}",
            $templates,
            $defaultIndex
        );

        return $helper->ask($input, $output, $choiceQuestion);
    }

    /**
     * Selection menu for different types of languages
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param array $types [description]
     *
     * @return string
     */
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

    /**
     * Selection menu to select a database
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return string
     */
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

    /**
     * Selection menu to select a database
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return string
     */
    protected function selectConnectionByEnviroment(InputInterface $input, OutputInterface $output): string
    {
        if (empty($_ENV['SELECTED_CONNECTION'])) {
            return $this->selectConnection($input, $output);
        } else {
            return $_ENV['SELECTED_CONNECTION'];
        }
    }

    /**
     * Selection menu to select a database
     *
     * @param InputInterface $input [InputInterface is the interface
     * implemented by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     * @param array $options [List of available migration types]
     *
     * @return string
     */
    protected function selectMigrationType(InputInterface $input, OutputInterface $output, array $options): string
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
