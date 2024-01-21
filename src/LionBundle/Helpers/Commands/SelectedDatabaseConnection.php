<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Helpers\Arr;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectedDatabaseConnection extends Command
{
    protected Arr $arr;

    /**
     * @required
     * */
    public function setArr(Arr $arr): SelectedDatabaseConnection
    {
        $this->arr = $arr;

        return $this;
    }

    protected function selectConnection(InputInterface $input, OutputInterface $output, QuestionHelper $helper): mixed
    {
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

        return $selectedConnection;
    }
}
