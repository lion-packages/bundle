<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use LionMailer\Mailer;
use LionMailer\DataMailer\Attach;

class SendMailTest extends TestCase {

	public function setUp(): void {

    }

    public function testSendMail(): void {
        $responseEmail = Mailer::from('example-dev@outlook.com')
            ->address('example-dev@outlook.com')
            ->replyTo('example-dev@outlook.com')
            ->subject('el subject')
            ->body('el body')
            ->altBody('el altbody')
            ->send();

		$this->assertEquals('success', $responseEmail->status);
    }

}