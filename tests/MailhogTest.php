<?php

declare(strict_types=1);

namespace Tests;

use Lion\Mailer\Exceptions\MailerAccountConfigException;
use Lion\Mailer\Mailer;
use Lion\Mailer\Priority;
use Lion\Test\Test;
use Tests\Providers\EmailProviderTrait;


class MailhogTest extends Test
{
    use EmailProviderTrait;


    protected function setUp(): void
    {
        $this->runMailer();
    }

    /**
     * @throws MailerAccountConfigException
     */
    public function testMail(): void
    {
        $this->assertTrue(
            Mailer::account($_ENV['MAIL_NAME'])
                ->subject('Test Priority')
                ->from('sleon@dev.com', 'Sleon')
                ->addAddress('jjerez@dev.com', 'Jjerez')
                ->body('Send Mailer')
                ->priority(Priority::HIGH)
                ->send()
        );
    }

    /**
     * @throws MailerAccountConfigException
     */
    public function testMailSupp(): void
    {
        $this->assertTrue(
            Mailer::account($_ENV['MAIL_NAME_SUPP'])
                ->subject('Test Priority')
                ->from('sleon@dev.com', 'Sleon')
                ->addAddress('jjerez@dev.com', 'Jjerez')
                ->body('Send Mailer')
                ->priority(Priority::HIGH)
                ->send()
        );
    }
}
