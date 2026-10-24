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

    public function asWifi(
        string|Closure|null $ssid = null,
        string|Closure|null $password = null,
        string|Closure $encryption = 'WPA',
        bool|Closure $hidden = false
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($ssid, $password, $encryption, $hidden) {
            $sVal = $this->evaluateFormattedValue($ssid, $raw) ?? $raw;
            $pVal = $this->evaluateFormattedValue($password, $raw);
            $tVal = $this->evaluateFormattedValue($encryption, $raw);
            $hVal = (bool) $this->evaluateFormattedValue($hidden, $raw);

            $s = $this->escapeWifiValue((string) $sVal);
            $t = strtoupper((string) ($tVal ?? 'WPA'));
            $h = $hVal ? 'true' : 'false';

            if ($pVal !== null && $pVal !== '') {
                $p = $this->escapeWifiValue((string) $pVal);
                $pPart = "P:{$p};";
            } elseif ($t === 'NOPASS' || $t === 'NONE') {
                $t = 'NOPASS';
                $pPart = '';
            } else {
                $pPart = 'P:;';
            }

            return "WIFI:S:{$s};T:{$t};{$pPart}H:{$h};;";
        });
    }

    public function asUrl(string|Closure|null $url = null): static
    {
        return $this->formatPayloadUsing(function ($raw) use ($url) {
            $value = $this->evaluateFormattedValue($url, $raw) ?? $raw;

            return (string) $value;
        });
    }

    public function asEmail(
        string|Closure|null $email = null,
        string|Closure|null $subject = null,
        string|Closure|null $body = null,
        string|Closure|null $cc = null,
        string|Closure|null $bcc = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($email, $subject, $body, $cc, $bcc) {
            $e = (string) ($this->evaluateFormattedValue($email, $raw) ?? $raw);
            $s = $this->evaluateFormattedValue($subject, $raw);
            $b = $this->evaluateFormattedValue($body, $raw);
            $c = $this->evaluateFormattedValue($cc, $raw);
            $bc = $this->evaluateFormattedValue($bcc, $raw);

            $params = [];
            if ($s !== null && $s !== '') {
                $params['subject'] = (string) $s;
            }
            if ($b !== null && $b !== '') {
                $params['body'] = (string) $b;
            }
            if ($c !== null && $c !== '') {
                $params['cc'] = (string) $c;
            }
            if ($bc !== null && $bc !== '') {
                $params['bcc'] = (string) $bc;
            }

            return 'mailto:'.$e.($params ? '?'.http_build_query($params) : '');
        });
    }

    public function asSms(string|Closure|null $phone = null, string|Closure|null $message = null): static
    {
        return $this->formatPayloadUsing(function ($raw) use ($phone, $message) {
            $p = (string) ($this->evaluateFormattedValue($phone, $raw) ?? $raw);
            $m = $this->evaluateFormattedValue($message, $raw);

            return 'smsto:'.$p.($m !== null && $m !== '' ? ':'.(string) $m : '');
        });
    }

    public function asPhoneNumber(string|Closure|null $phone = null): static
    {
        return $this->formatPayloadUsing(function ($raw) use ($phone) {
            $p = (string) ($this->evaluateFormattedValue($phone, $raw) ?? $raw);

            return 'tel:'.$p;
        });
    }

    public function asBitcoin(
        string|Closure|null $address = null,
        float|string|Closure|null $amount = null,
        string|Closure|null $label = null,
        string|Closure|null $message = null,
        string|Closure|null $returnAddress = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($address, $amount, $label, $message, $returnAddress) {
            $addr = trim((string) ($this->evaluateFormattedValue($address, $raw) ?? $raw));
            $amt = $this->evaluateFormattedValue($amount, $raw);
            $lbl = $this->evaluateFormattedValue($label, $raw);
            $msg = $this->evaluateFormattedValue($message, $raw);
            $ret = $this->evaluateFormattedValue($returnAddress, $raw);

            $params = [];
            if ($amt !== null && $amt !== '') {
                $params['amount'] = (string) $amt;
            }
            if ($lbl !== null && $lbl !== '') {
                $params['label'] = (string) $lbl;
            }
            if ($msg !== null && $msg !== '') {
                $params['message'] = (string) $msg;
            }
            if ($ret !== null && $ret !== '') {
                $params['r'] = (string) $ret;
            }

            $query = $params ? '?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986) : '';

            return "bitcoin:{$addr}{$query}";
        });
    }

    public function asEthereum(
        string|Closure|null $address = null,
        float|string|Closure|null $value = null,
        float|string|Closure|null $gas = null,
        float|string|Closure|null $gasLimit = null,
        float|string|Closure|null $gasPrice = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($address, $value, $gas, $gasLimit, $gasPrice) {
            $addr = trim((string) ($this->evaluateFormattedValue($address, $raw) ?? $raw));
            $val = $this->evaluateFormattedValue($value, $raw);
            $g = $this->evaluateFormattedValue($gas, $raw);
            $gl = $this->evaluateFormattedValue($gasLimit, $raw);
            $gp = $this->evaluateFormattedValue($gasPrice, $raw);

            $params = [];
            if ($val !== null && $val !== '') {
                $params['value'] = (string) $val;
            }
            if ($g !== null && $g !== '') {
                $params['gas'] = (string) $g;
            }
            if ($gl !== null && $gl !== '') {
                $params['gasLimit'] = (string) $gl;
            }
            if ($gp !== null && $gp !== '') {
                $params['gasPrice'] = (string) $gp;
            }

            $query = $params ? '?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986) : '';

            return "ethereum:{$addr}{$query}";
        });
    }

    /**
     * @param  array<string, string>|string|null  $name
     */
    public function asVCard(
        string|array|Closure|null $name = null,
        string|Closure|null $fullName = null,
        string|Closure|null $company = null,
        string|Closure|null $title = null,
        string|Closure|null $email = null,
        string|Closure|null $phone = null,
        string|Closure|null $phoneWork = null,
        string|Closure|null $phoneCell = null,
        string|Closure|null $address = null,
        string|Closure|null $url = null,
        string|Closure|null $note = null,
        string|Closure|null $birthday = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use (
            $name,
            $fullName,
            $company,
            $title,
            $email,
            $phone,
            $phoneWork,
            $phoneCell,
            $address,
            $url,
            $note,
            $birthday
        ) {
            $evaluatedName = $this->evaluateFormattedValue($name, $raw);
            $evaluatedFullName = $this->evaluateFormattedValue($fullName, $raw);

            if (is_array($evaluatedName)) {
                $last = $evaluatedName['last'] ?? '';
                $first = $evaluatedName['first'] ?? '';
                $middle = $evaluatedName['middle'] ?? '';
                $prefix = $evaluatedName['prefix'] ?? '';
                $suffix = $evaluatedName['suffix'] ?? '';
                $nFormatted = "{$last};{$first};{$middle};{$prefix};{$suffix}";
                $fn = $evaluatedFullName ?? trim("{$prefix} {$first} {$middle} {$last} {$suffix}");
            } else {
                $stringName = (string) ($evaluatedName ?? $raw);
                $fn = $evaluatedFullName ?? $stringName;
                $parts = explode(' ', $stringName, 2);
                $first = $parts[0] ?? '';
                $last = $parts[1] ?? '';
                $nFormatted = "{$last};{$first};;;";
            }

            $companyVal = $this->evaluateFormattedValue($company, $raw);
            $titleVal = $this->evaluateFormattedValue($title, $raw);
            $emailVal = $this->evaluateFormattedValue($email, $raw);
            $phoneVal = $this->evaluateFormattedValue($phone, $raw);
            $phoneWorkVal = $this->evaluateFormattedValue($phoneWork, $raw);
            $phoneCellVal = $this->evaluateFormattedValue($phoneCell, $raw);
            $addressVal = $this->evaluateFormattedValue($address, $raw);
            $urlVal = $this->evaluateFormattedValue($url, $raw);
            $noteVal = $this->evaluateFormattedValue($note, $raw);
            $birthdayVal = $this->evaluateFormattedValue($birthday, $raw);

            $lines = [
                'BEGIN:VCARD',
                'VERSION:3.0',
                'N:'.$nFormatted,
                'FN:'.$this->escapeVCardValue((string) $fn),
            ];

            if ($companyVal !== null && $companyVal !== '') {
                $lines[] = 'ORG:'.$this->escapeVCardValue((string) $companyVal);
            }

            if ($titleVal !== null && $titleVal !== '') {
                $lines[] = 'TITLE:'.$this->escapeVCardValue((string) $titleVal);
            }

            if ($emailVal !== null && $emailVal !== '') {
                $lines[] = 'EMAIL;type=INTERNET:'.$this->escapeVCardValue((string) $emailVal);
            }

            if ($phoneVal !== null && $phoneVal !== '') {
                $lines[] = 'TEL;type=VOICE:'.$this->escapeVCardValue((string) $phoneVal);
            }

            if ($phoneWorkVal !== null && $phoneWorkVal !== '') {
                $lines[] = 'TEL;type=WORK:'.$this->escapeVCardValue((string) $phoneWorkVal);
            }

            if ($phoneCellVal !== null && $phoneCellVal !== '') {
                $lines[] = 'TEL;type=CELL:'.$this->escapeVCardValue((string) $phoneCellVal);
            }

            if ($addressVal !== null && $addressVal !== '') {
                $lines[] = 'ADR:;;'.$this->escapeVCardValue((string) $addressVal).';;;;';
            }

            if ($urlVal !== null && $urlVal !== '') {
                $lines[] = 'URL:'.$this->escapeVCardValue((string) $urlVal);
            }

            if ($noteVal !== null && $noteVal !== '') {
                $lines[] = 'NOTE:'.$this->escapeVCardValue((string) $noteVal);
            }

            if ($birthdayVal !== null && $birthdayVal !== '') {
                $lines[] = 'BDAY:'.$this->escapeVCardValue((string) $birthdayVal);
            }

            $lines[] = 'END:VCARD';
            $lines[] = '';

            return implode("\r\n", $lines);
        });
    }

    /**
     * @param  array<string, string>|string|null  $name
     */
    public function asMeCard(
        string|array|Closure|null $name = null,
        string|Closure|null $phone = null,
        string|Closure|null $email = null,
        string|Closure|null $url = null,
        string|Closure|null $address = null,
        string|Closure|null $note = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($name, $phone, $email, $url, $address, $note) {
            $evaluatedName = $this->evaluateFormattedValue($name, $raw);

            if (is_array($evaluatedName)) {
                $n = $evaluatedName['name'] ?? (string) $raw;
                $p = $evaluatedName['phone'] ?? null;
                $e = $evaluatedName['email'] ?? null;
                $u = $evaluatedName['url'] ?? null;
                $a = $evaluatedName['address'] ?? null;
                $nt = $evaluatedName['note'] ?? null;
            } else {
                $n = (string) ($evaluatedName ?? $raw);
                $p = $this->evaluateFormattedValue($phone, $raw);
                $e = $this->evaluateFormattedValue($email, $raw);
                $u = $this->evaluateFormattedValue($url, $raw);
                $a = $this->evaluateFormattedValue($address, $raw);
                $nt = $this->evaluateFormattedValue($note, $raw);
            }

            $parts = ['MECARD:N:'.$this->escapeMeCardValue($n).';'];

            if ($p !== null && $p !== '') {
                $parts[] = 'TEL:'.$this->escapeMeCardValue((string) $p).';';
            }
            if ($e !== null && $e !== '') {
                $parts[] = 'EMAIL:'.$this->escapeMeCardValue((string) $e).';';
            }
            if ($u !== null && $u !== '') {
                $parts[] = 'URL:'.$this->escapeMeCardValue((string) $u).';';
            }
            if ($a !== null && $a !== '') {
                $parts[] = 'ADR:'.$this->escapeMeCardValue((string) $a).';';
            }
            if ($nt !== null && $nt !== '') {
                $parts[] = 'NOTE:'.$this->escapeMeCardValue((string) $nt).';';
            }

            return implode('', $parts).';';
        });
    }

    public function asGeo(
        float|string|Closure $latitude,
        float|string|Closure $longitude,
        string|Closure|null $name = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($latitude, $longitude, $name) {
            $lat = $this->evaluateFormattedValue($latitude, $raw);
            $lng = $this->evaluateFormattedValue($longitude, $raw);
            $queryName = $this->evaluateFormattedValue($name, $raw);

            $geo = 'geo:'.$lat.','.$lng;
            if ($queryName !== null && $queryName !== '') {
                $geo .= '?q='.urlencode((string) $queryName);
            }

            return $geo;
        });
    }

    public function resolveFormattedPayload(mixed $raw): ?string
    {
        if ($this->payloadFormatter) {
            return (string) ($this->payloadFormatter)($raw);
        }

        return $raw !== null ? (string) $raw : null;
    }

    protected function evaluateFormattedValue(mixed $value, mixed $raw): mixed
    {
        if ($value instanceof Closure) {
            return $this->evaluate($value, ['state' => $raw, 'raw' => $raw, 'record' => $raw]);
        }

        return $value;
    }

    protected function escapeWifiValue(string $value): string
    {
        return strtr($value, [
            '\\' => '\\\\',
            ';' => '\\;',
            ':' => '\\:',
            ',' => '\\,',
        ]);
    }

    protected function escapeVCardValue(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return strtr($value, [
            '\\' => '\\\\',
            ';' => '\\;',
            ',' => '\\,',
            "\n" => '\\n',
        ]);
    }

    protected function escapeMeCardValue(string $value): string
    {
        return strtr($value, [
            '\\' => '\\\\',
            ';' => '\\;',
            ':' => '\\:',
            ',' => '\\,',
        ]);
    }
}
