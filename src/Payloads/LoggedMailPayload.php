<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Spatie\Ray\Payloads\Payload;
use ZBateson\MailMimeParser\Header\AddressHeader;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\Header\Part\AddressPart;
use ZBateson\MailMimeParser\IMessage;
use ZBateson\MailMimeParser\MailMimeParser;

final class LoggedMailPayload extends Payload
{
    public function __construct(private readonly string $html, private readonly array $from = [], private readonly ?string $subject = null, private readonly array $to = [], private readonly array $cc = [], private readonly array $bcc = []) {}

    public static function forLoggedMail(string $loggedMail): self
    {
        $parser = new MailMimeParser;

        $message = $parser->parse($loggedMail, true);

        // get the part in $loggedMail that starts with <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0

        $content = self::getMailContent($loggedMail, $message);

        return new self(
            $content,
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::FROM)),
            $message->getHeaderValue(HeaderConsts::SUBJECT),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::TO)),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::CC)),
            self::convertHeaderToPersons($message->getHeader(HeaderConsts::BCC)),
        );
    }

    public function getType(): string
    {
        return 'mailable';
    }

    public function getContent(): array
    {
        return [
            'html' => $this->sanitizeHtml($this->html),
            'subject' => $this->subject,
            'from' => $this->from,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
        ];
    }

    private static function getMailContent(string $loggedMail, IMessage $message): string
    {
        $startOfHtml = mb_strpos($loggedMail, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0');

        if ($startOfHtml === 0 || $startOfHtml === false) {
            return $message->getContent() ?? $message->getHtmlContent() ?? '';
        }

        return mb_substr($loggedMail, $startOfHtml) ?? '';
    }

    private static function convertHeaderToPersons(?AddressHeader $header): array
    {
        if (! $header instanceof AddressHeader) {
            return [];
        }

        return array_map(
            fn (AddressPart $address): array => [
                'name' => $address->getName(),
                'email' => $address->getEmail(),
            ],
            $header->getAddresses()
        );
    }

    private function sanitizeHtml(string $html): string
    {
        $needle = 'Content-Type: text/html; charset=utf-8 Content-Transfer-Encoding: quoted-printable';

        if (mb_strpos($html, $needle) !== false) {
            return mb_substr($html, mb_strpos($html, $needle));
        }

        return $html;
    }
}
