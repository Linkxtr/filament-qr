<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr\Concerns;

use Closure;
use Stringable;

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
        return $this->formatPayloadUsing(function ($raw) use ($ssid, $password, $encryption, $hidden): string {
            $sVal = $this->evaluateFormattedValue($ssid, $raw) ?? $raw;
            $pVal = $this->evaluateFormattedValue($password, $raw);
            $tVal = $this->evaluateFormattedValue($encryption, $raw);
            $hVal = (bool) $this->evaluateFormattedValue($hidden, $raw);

            $s = $this->escapeWifiValue($this->formatString($sVal));
            $t = strtoupper($this->formatString($tVal, 'WPA'));
            $h = $hVal ? 'true' : 'false';

            if ($pVal !== null && $pVal !== '') {
                $p = $this->escapeWifiValue($this->formatString($pVal));
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

            return $this->formatString($value);
        });
    }

    public function asEmail(
        string|Closure|null $email = null,
        string|Closure|null $subject = null,
        string|Closure|null $body = null,
        string|Closure|null $cc = null,
        string|Closure|null $bcc = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($email, $subject, $body, $cc, $bcc): string {
            $e = $this->formatString($this->evaluateFormattedValue($email, $raw) ?? $raw);
            $s = $this->evaluateFormattedValue($subject, $raw);
            $b = $this->evaluateFormattedValue($body, $raw);
            $c = $this->evaluateFormattedValue($cc, $raw);
            $bc = $this->evaluateFormattedValue($bcc, $raw);

            $params = [];
            if ($s !== null && $s !== '') {
                $params['subject'] = $this->formatString($s);
            }

            if ($b !== null && $b !== '') {
                $params['body'] = $this->formatString($b);
            }

            if ($c !== null && $c !== '') {
                $params['cc'] = $this->formatString($c);
            }

            if ($bc !== null && $bc !== '') {
                $params['bcc'] = $this->formatString($bc);
            }

            return 'mailto:'.$e.($params !== [] ? '?'.http_build_query($params) : '');
        });
    }

    public function asSms(string|Closure|null $phone = null, string|Closure|null $message = null): static
    {
        return $this->formatPayloadUsing(function ($raw) use ($phone, $message): string {
            $p = $this->formatString($this->evaluateFormattedValue($phone, $raw) ?? $raw);
            $m = $this->evaluateFormattedValue($message, $raw);

            return 'smsto:'.$p.($m !== null && $m !== '' ? ':'.$this->formatString($m) : '');
        });
    }

    public function asPhoneNumber(string|Closure|null $phone = null): static
    {
        return $this->formatPayloadUsing(function ($raw) use ($phone): string {
            $p = $this->formatString($this->evaluateFormattedValue($phone, $raw) ?? $raw);

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
        return $this->formatPayloadUsing(function ($raw) use ($address, $amount, $label, $message, $returnAddress): string {
            $addr = trim($this->formatString($this->evaluateFormattedValue($address, $raw) ?? $raw));
            $amt = $this->evaluateFormattedValue($amount, $raw);
            $lbl = $this->evaluateFormattedValue($label, $raw);
            $msg = $this->evaluateFormattedValue($message, $raw);
            $ret = $this->evaluateFormattedValue($returnAddress, $raw);

            $params = [];
            if ($amt !== null && $amt !== '') {
                $params['amount'] = $this->formatString($amt);
            }

            if ($lbl !== null && $lbl !== '') {
                $params['label'] = $this->formatString($lbl);
            }

            if ($msg !== null && $msg !== '') {
                $params['message'] = $this->formatString($msg);
            }

            if ($ret !== null && $ret !== '') {
                $params['r'] = $this->formatString($ret);
            }

            $query = $params !== [] ? '?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986) : '';

            return 'bitcoin:'.$addr.$query;
        });
    }

    public function asBtc(
        string|Closure|null $address = null,
        float|string|Closure|null $amount = null,
        string|Closure|null $label = null,
        string|Closure|null $message = null,
        string|Closure|null $returnAddress = null
    ): static {
        return $this->asBitcoin($address, $amount, $label, $message, $returnAddress);
    }

    public function asEthereum(
        string|Closure|null $address = null,
        float|string|Closure|null $amount = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($address, $amount): string {
            $addr = trim($this->formatString($this->evaluateFormattedValue($address, $raw) ?? $raw));
            $amt = $this->evaluateFormattedValue($amount, $raw);

            $query = ($amt !== null && $amt !== '') ? '?'.http_build_query(['amount' => $this->formatString($amt)], '', '&', PHP_QUERY_RFC3986) : '';

            return 'ethereum:'.$addr.$query;
        });
    }

    public function asEth(
        string|Closure|null $address = null,
        float|string|Closure|null $amount = null
    ): static {
        return $this->asEthereum($address, $amount);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function asCrypto(
        string|Closure $type,
        string|Closure|null $address = null,
        float|string|Closure|null $amount = null,
        array|Closure $extra = []
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($type, $address, $amount, $extra): string {
            $scheme = strtolower($this->formatString($this->evaluateFormattedValue($type, $raw)));
            $addr = trim($this->formatString($this->evaluateFormattedValue($address, $raw) ?? $raw));
            $amt = $this->evaluateFormattedValue($amount, $raw);
            $evaluatedExtra = $this->evaluateFormattedValue($extra, $raw);
            $extraParams = is_array($evaluatedExtra) ? $evaluatedExtra : [];

            $params = [];
            if ($amt !== null && $amt !== '') {
                $params['amount'] = $this->formatString($amt);
            }

            foreach ($extraParams as $k => $v) {
                if ($v !== null && $v !== '') {
                    $keyStr = is_string($k) ? $k : (string) $k;
                    $params[$keyStr] = $this->formatString($v);
                }
            }

            $query = $params !== [] ? '?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986) : '';

            return $scheme.':'.$addr.$query;
        });
    }

    /**
     * @param  array<string, string>|string|null  $name
     */
    public function asVCard(
        string|array|Closure|null $name = null,
        string|Closure|null $phone = null,
        string|Closure|null $email = null,
        string|Closure|null $company = null,
        string|Closure|null $job = null,
        string|Closure|null $firstName = null,
        string|Closure|null $lastName = null,
        string|Closure|null $address = null,
        string|Closure|null $url = null,
        string|Closure|null $note = null,
        string|Closure|null $birthday = null,
        string|Closure|null $version = '4.0',
        string|Closure|null $emailWork = null,
        string|Closure|null $emailHome = null,
        string|Closure|null $phoneWork = null,
        string|Closure|null $phoneHome = null,
        string|Closure|null $phoneCell = null,
        string|Closure|null $role = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use (
            $name, $phone, $email, $company, $job, $firstName, $lastName,
            $address, $url, $note, $birthday, $version, $emailWork, $emailHome,
            $phoneWork, $phoneHome, $phoneCell, $role
        ): string {
            $evaluatedName = $this->evaluateFormattedValue($name, $raw);

            // Support passing associative array of vCard attributes
            if (is_array($evaluatedName)) {
                $data = $evaluatedName;
                $fn = isset($data['name']) ? $this->formatString($data['name']) : (isset($data['fn']) ? $this->formatString($data['fn']) : $this->formatString($raw));
                $firstNameVal = $data['firstName'] ?? $data['first_name'] ?? null;
                $lastNameVal = $data['lastName'] ?? $data['last_name'] ?? null;
                $companyVal = $data['company'] ?? $data['org'] ?? null;
                $jobVal = $data['job'] ?? $data['title'] ?? null;
                $roleVal = $data['role'] ?? null;
                $emailVal = $data['email'] ?? null;
                $emailWorkVal = $data['emailWork'] ?? $data['email_work'] ?? null;
                $emailHomeVal = $data['emailHome'] ?? $data['email_home'] ?? null;
                $phoneVal = $data['phone'] ?? null;
                $phoneWorkVal = $data['phoneWork'] ?? $data['phone_work'] ?? null;
                $phoneHomeVal = $data['phoneHome'] ?? $data['phone_home'] ?? null;
                $phoneCellVal = $data['phoneCell'] ?? $data['phone_cell'] ?? null;
                $addressVal = $data['address'] ?? $data['adr'] ?? null;
                $urlVal = $data['url'] ?? null;
                $noteVal = $data['note'] ?? null;
                $birthdayVal = $data['birthday'] ?? $data['bday'] ?? null;
                $vCardVersion = isset($data['version']) ? $this->formatString($data['version'], '4.0') : '4.0';
            } else {
                $fn = $this->formatString($evaluatedName ?? $raw);
                $firstNameVal = $this->evaluateFormattedValue($firstName, $raw);
                $lastNameVal = $this->evaluateFormattedValue($lastName, $raw);
                $companyVal = $this->evaluateFormattedValue($company, $raw);
                $jobVal = $this->evaluateFormattedValue($job, $raw);
                $roleVal = $this->evaluateFormattedValue($role, $raw);
                $emailVal = $this->evaluateFormattedValue($email, $raw);
                $emailWorkVal = $this->evaluateFormattedValue($emailWork, $raw);
                $emailHomeVal = $this->evaluateFormattedValue($emailHome, $raw);
                $phoneVal = $this->evaluateFormattedValue($phone, $raw);
                $phoneWorkVal = $this->evaluateFormattedValue($phoneWork, $raw);
                $phoneHomeVal = $this->evaluateFormattedValue($phoneHome, $raw);
                $phoneCellVal = $this->evaluateFormattedValue($phoneCell, $raw);
                $addressVal = $this->evaluateFormattedValue($address, $raw);
                $urlVal = $this->evaluateFormattedValue($url, $raw);
                $noteVal = $this->evaluateFormattedValue($note, $raw);
                $birthdayVal = $this->evaluateFormattedValue($birthday, $raw);
                $vCardVersion = $this->formatString($this->evaluateFormattedValue($version, $raw) ?? '4.0', '4.0');
            }

            $lines = [
                'BEGIN:VCARD',
                'VERSION:'.$vCardVersion,
                'FN:'.$this->escapeVCardValue($fn),
            ];

            if ($firstNameVal !== null || $lastNameVal !== null) {
                $lines[] = 'N:'.$this->escapeVCardValue($this->formatString($lastNameVal)).';'.
                    $this->escapeVCardValue($this->formatString($firstNameVal)).';;;';
            }

            if ($companyVal !== null && $companyVal !== '') {
                $lines[] = 'ORG:'.$this->escapeVCardValue($this->formatString($companyVal));
            }

            if ($jobVal !== null && $jobVal !== '') {
                $lines[] = 'TITLE:'.$this->escapeVCardValue($this->formatString($jobVal));
            }

            if ($roleVal !== null && $roleVal !== '') {
                $lines[] = 'ROLE:'.$this->escapeVCardValue($this->formatString($roleVal));
            }

            if ($emailVal !== null && $emailVal !== '') {
                $emailPrefix = $vCardVersion === '3.0' ? 'EMAIL;type=INTERNET:' : 'EMAIL:';
                $lines[] = $emailPrefix.$this->escapeVCardValue($this->formatString($emailVal));
            }

            if ($emailWorkVal !== null && $emailWorkVal !== '') {
                $lines[] = 'EMAIL;type=WORK:'.$this->escapeVCardValue($this->formatString($emailWorkVal));
            }

            if ($emailHomeVal !== null && $emailHomeVal !== '') {
                $lines[] = 'EMAIL;type=HOME:'.$this->escapeVCardValue($this->formatString($emailHomeVal));
            }

            if ($phoneVal !== null && $phoneVal !== '') {
                $lines[] = 'TEL:'.$this->escapeVCardValue($this->formatString($phoneVal));
            }

            if ($phoneWorkVal !== null && $phoneWorkVal !== '') {
                $lines[] = 'TEL;type=WORK:'.$this->escapeVCardValue($this->formatString($phoneWorkVal));
            }

            if ($phoneHomeVal !== null && $phoneHomeVal !== '') {
                $lines[] = 'TEL;type=HOME:'.$this->escapeVCardValue($this->formatString($phoneHomeVal));
            }

            if ($phoneCellVal !== null && $phoneCellVal !== '') {
                $lines[] = 'TEL;type=CELL:'.$this->escapeVCardValue($this->formatString($phoneCellVal));
            }

            if ($addressVal !== null && $addressVal !== '') {
                $lines[] = 'ADR:;;'.$this->escapeVCardValue($this->formatString($addressVal)).';;;;';
            }

            if ($urlVal !== null && $urlVal !== '') {
                $lines[] = 'URL:'.$this->escapeVCardValue($this->formatString($urlVal));
            }

            if ($noteVal !== null && $noteVal !== '') {
                $lines[] = 'NOTE:'.$this->escapeVCardValue($this->formatString($noteVal));
            }

            if ($birthdayVal !== null && $birthdayVal !== '') {
                $lines[] = 'BDAY:'.$this->escapeVCardValue($this->formatString($birthdayVal));
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
        return $this->formatPayloadUsing(function ($raw) use ($name, $phone, $email, $url, $address, $note): string {
            $evaluatedName = $this->evaluateFormattedValue($name, $raw);

            if (is_array($evaluatedName)) {
                $n = isset($evaluatedName['name']) ? $this->formatString($evaluatedName['name']) : $this->formatString($raw);
                $p = $evaluatedName['phone'] ?? null;
                $e = $evaluatedName['email'] ?? null;
                $u = $evaluatedName['url'] ?? null;
                $a = $evaluatedName['address'] ?? null;
                $nt = $evaluatedName['note'] ?? null;
            } else {
                $n = $this->formatString($evaluatedName ?? $raw);
                $p = $this->evaluateFormattedValue($phone, $raw);
                $e = $this->evaluateFormattedValue($email, $raw);
                $u = $this->evaluateFormattedValue($url, $raw);
                $a = $this->evaluateFormattedValue($address, $raw);
                $nt = $this->evaluateFormattedValue($note, $raw);
            }

            $parts = ['MECARD:N:'.$this->escapeMeCardValue($n).';'];

            if ($p !== null && $p !== '') {
                $parts[] = 'TEL:'.$this->escapeMeCardValue($this->formatString($p)).';';
            }

            if ($e !== null && $e !== '') {
                $parts[] = 'EMAIL:'.$this->escapeMeCardValue($this->formatString($e)).';';
            }

            if ($u !== null && $u !== '') {
                $parts[] = 'URL:'.$this->escapeMeCardValue($this->formatString($u)).';';
            }

            if ($a !== null && $a !== '') {
                $parts[] = 'ADR:'.$this->escapeMeCardValue($this->formatString($a)).';';
            }

            if ($nt !== null && $nt !== '') {
                $parts[] = 'NOTE:'.$this->escapeMeCardValue($this->formatString($nt)).';';
            }

            return implode('', $parts).';';
        });
    }

    public function asGeo(
        float|string|Closure $latitude,
        float|string|Closure $longitude,
        string|Closure|null $name = null
    ): static {
        return $this->formatPayloadUsing(function ($raw) use ($latitude, $longitude, $name): string {
            $lat = $this->formatString($this->evaluateFormattedValue($latitude, $raw));
            $lng = $this->formatString($this->evaluateFormattedValue($longitude, $raw));
            $queryName = $this->evaluateFormattedValue($name, $raw);

            $geo = "geo:{$lat},{$lng}";
            if ($queryName !== null && $queryName !== '') {
                $geo .= '?q='.urlencode($this->formatString($queryName));
            }

            return $geo;
        });
    }

    public function resolveFormattedPayload(mixed $raw): ?string
    {
        if ($this->payloadFormatter) {
            return $this->formatString(($this->payloadFormatter)($raw));
        }

        return $raw !== null ? $this->formatString($raw) : null;
    }

    protected function formatString(mixed $value, string $default = ''): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return $default;
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
