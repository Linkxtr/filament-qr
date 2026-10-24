<?php

namespace Linkxtr\FilamentQr\Concerns;

use Closure;

trait HasPayloadFormatters
{
    protected ?Closure $payloadFormatter = null;

    public function formatPayloadUsing(Closure $callback): static
    {
        $this->payloadFormatter = $callback;

        return $this;
    }

    public function asWifi(string | Closure $ssid, string | Closure | null $password = null, string | Closure $encryption = 'WPA', bool | Closure $hidden = false): static
    {
        return $this->formatPayloadUsing(function () use ($ssid, $password, $encryption, $hidden) {
            $s = $this->evaluate($ssid);
            $p = $this->evaluate($password);
            $t = $this->evaluate($encryption);
            $h = $this->evaluate($hidden) ? 'true' : 'false';

            return "WIFI:S:{$s};T:{$t};P:{$p};H:{$h};;";
        });
    }

    public function asUrl(string | Closure $url): static
    {
        return $this->formatPayloadUsing(fn () => (string) $this->evaluate($url));
    }

    public function asEmail(string | Closure $email, string | Closure | null $subject = null, string | Closure | null $body = null): static
    {
        return $this->formatPayloadUsing(function () use ($email, $subject, $body) {
            $e = $this->evaluate($email);
            $s = $this->evaluate($subject);
            $b = $this->evaluate($body);

            $params = [];
            if ($s) {
                $params['subject'] = $s;
            }
            if ($b) {
                $params['body'] = $b;
            }

            return 'mailto:' . $e . ($params ? '?' . http_build_query($params) : '');
        });
    }

    public function asSms(string | Closure $phone, string | Closure | null $message = null): static
    {
        return $this->formatPayloadUsing(function () use ($phone, $message) {
            $p = $this->evaluate($phone);
            $m = $this->evaluate($message);

            return 'smsto:' . $p . ($m ? ':' . $m : '');
        });
    }

    public function resolveFormattedPayload(mixed $raw): ?string
    {
        if ($this->payloadFormatter) {
            return (string) ($this->payloadFormatter)($raw);
        }

        return $raw !== null ? (string) $raw : null;
    }
}
