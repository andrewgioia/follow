<?php
require __DIR__.'/../inc/class.php';

class FixtureFollow extends Follow
{
    public array $responses = [];

    protected function request(): array
    {
        if (!$this->responses) {
            throw new RuntimeException('Unexpected request: '.$this->url);
        }
        return array_shift($this->responses);
    }
}

function response(int $code, string $next = '', string $error = '', bool $retryable = true): array
{
    return ['code' => $code, 'next' => $next, 'error' => $error,
        'retryable' => $retryable, 'headers' => []];
}

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    $checks++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// A certificate failure must preserve the tracking token and allow the next
// hop to return to HTTPS. Only actual requests should appear in the trace.
$url = 'https://tracker.test/click?token=a%2Fb-2B&target=https%3A%2F%2Fsite.test#part';
$f = new FixtureFollow($url);
$f->responses = [response(0, '', 'Certificate hostname mismatch'),
    response(302, 'https://site.test/final'), response(204)];
check($f->getHttpCode(), 'HTTPS failure should schedule fallback');
check($f->next === 'http://'.substr($url, 8), 'Fallback must preserve URL bytes');
check(!$f->error && !$f->redirect, 'Fallback is not an HTTP redirect or terminal error');
check($f->path[1]['fallback'] && $f->path[1]['error'] !== '', 'Original failure must be visible');
$f->url = $f->next;
$f->getHttpCode();
check($f->redirect && $f->code === 302, 'HTTP fallback should follow ordinary redirects');
$f->url = $f->next;
$f->getHttpCode();
check($f->code === 204 && !$f->error && $f->next === '', '204 must terminate without stale state');
check(count($f->path) === 3 && $f->getFinalRedirect() === 'https://site.test/final', 'No phantom final row');

foreach ([200, 201, 204, 206, 299, 404, 500, 302] as $code) {
    $f = new FixtureFollow('https://site.test/');
    $f->responses = [response($code)];
    $f->getHttpCode();
    check($f->next === '' && !$f->path[1]['fallback'], "$code must not downgrade");
    check((bool) $f->error === ($code >= 300), "$code terminal status");
}

foreach ([
    'HTTPS://site.test:443/a?port=:443' => 'http://site.test/a?port=:443',
    'https://site.test:8443/a' => 'http://site.test:8443/a',
    'https://[::1]:443/a' => 'http://[::1]/a'
] as $url => $expected) {
    $f = new FixtureFollow($url);
    $f->responses = [response(0, '', 'TLS failure')];
    $f->getHttpCode();
    check($f->next === $expected, 'Port and scheme handling: '.$url);
}

$f = new FixtureFollow('https://site.test/');
$f->responses = [response(0, '', 'TLS failure'), response(0, '', 'Connection refused')];
$f->getHttpCode();
$f->url = $f->next;
check(!$f->getHttpCode() && $f->error['message'] === 'Connection refused', 'Failed HTTP retry must stop');
check($f->path[1]['error'] === 'TLS failure' && count($f->path) === 2, 'Keep both failures');
check($f->next === '', 'HTTP must never retry itself');

foreach ([response(200, '', 'Partial transfer'), response(0, '', 'Initialization failure', false)] as $result) {
    $f = new FixtureFollow('https://site.test/');
    $f->responses = [$result];
    check(!$f->getHttpCode() && $f->next === '', 'Do not retry partial responses or initialization errors');
}

echo "Passed $checks checks.\n";
