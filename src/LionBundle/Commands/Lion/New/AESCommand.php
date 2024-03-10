<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Command\Command;
use Lion\Security\AES;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class AESCommand extends Command
{
    const AES_METHODS = [AES::AES_256_CBC];

    private AES $aes;

    /**
     * @required
     */
    public function setAES(AES $aes): AESCommand
    {
        $this->aes = $aes;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName('new:aes')
            ->setDescription("Command required to create 'KEY' and 'IV' keys for AES encryptions");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $aesMethods = [...self::AES_METHODS];

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $aesMethod = $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                ('Select project ' . $this->warningOutput('(default: ' . reset($aesMethods) . ')')),
                $aesMethods,
                0
            )
        );

        $config = $this->aes->create($aesMethod)->toObject()->get();

        $output->writeln($this->errorOutput("\t>>  AES METHOD: {$aesMethod}"));
        $output->writeln($this->warningOutput("\t>>  AES KEY: {$config->key}"));
        $output->writeln($this->warningOutput("\t>>  AES IV: {$config->iv}"));
        $output->writeln($this->successOutput("\t>>  Keys created successfully"));

        return Command::SUCCESS;
    }
}
