<?php

namespace App\Console\Framework\Email;

use LionMailer\SettingsMailServices;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowAccountsCommand extends Command
{
	protected static $defaultName = "email:show";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this
            ->setDescription("Command required to display available email accounts");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
		$accounts = SettingsMailServices::getAccounts();
        $size = arr->of($accounts['accounts'])->length();
        $list_accounts = [];

        foreach ($accounts['accounts'] as $key => $account) {
            $item = [
                'services' => arr->of($account['services'])->join(" - "),
                'debug' => "<fg=#FFB63E>{$account['debug']}</>",
                'host' => "<fg=#FFB63E>{$account['host']}</>",
                'encryption' => "<fg=#FFB63E>{$account['encryption']}</>",
                'port' => "<fg=#FFB63E>{$account['port']}</>",
                'name' => "<fg=#FFB63E>{$account['name']}</>",
                'account' => $account['account']
            ];

            if ($account['name'] === $accounts['default']) {
                $item['name'] = "{$account['name']} <fg=#FFB63E>(default)</>";
            } else {
                $item['name'] = $account['name'];
            }

            $list_accounts[] = $item;
        }

        (new Table($output))
            ->setHeaderTitle('<info> EMAILS </info>')
            ->setHeaders(['SERVICES', 'DEBUG', 'HOST', 'ENCRYPTION', 'PORT', 'NAME', 'ACCOUNT'])
            ->setFooterTitle(
                $size > 1
                    ? "<info> Showing [" . $size . "] accounts </info>"
                    : ($size === 1
                        ? "<info> showing a single account </info>"
                        : "<info> No accounts available </info>"
                    )
            )
            ->setRows($list_accounts)
            ->render();

		return Command::SUCCESS;
	}
}
