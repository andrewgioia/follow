<?php
class Follow
{
    // constants
    const USER_AGENT = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36";
    const ERROR_CURL_INIT = "Couldn't initialize a curl handle";
    const ERROR_CURL_CODE = "Could not curl_getinfo the HTTP code";
    const ERROR_CURL_REDIRECT = "Could not curl_getinfo the redirect URL";

    // properties
    public int $code;
    public int $step;
    public bool $redirect;
    public array $path;
    public array $error;
    public array $headers;
    public string $url;
    public string $next;

    // constructor
    public function __construct(string $url)
    {
        $this->url = $url;
        $this->next = '';
        $this->code = 0;
        $this->step = 1;
        $this->redirect = false;
        $this->error = [];
        $this->headers = [];
        $this->path = [];
    }

    // create an error message
    private function setError(array $error): void
    {
        $this->error = $error;
    }

    // update a path entry
    public function updatePath(int $step, string $key, $value): void
    {
        $this->path[$step][$key] = $value;
    }

    // Make exactly one request; the caller follows redirects and HTTP fallbacks.
    public function getHttpCode(): bool
    {
        $this->code = 0;
        $this->next = '';
        $this->redirect = false;
        $this->error = [];
        $this->headers = [];
        $this->step = count($this->path) + 1;
        $this->path[$this->step] = [
            'step' => $this->step,
            'url' => $this->url,
            'code' => null,
            'headers' => [],
            'next' => '',
            'fallback' => false,
            'error' => ''
        ];

        $result = $this->request();
        $this->code = $result['code'];
        $this->headers = $result['headers'];
        $this->updatePath($this->step, 'code', $this->code ?: null);
        $this->updatePath($this->step, 'headers', $this->headers);

        if ($result['error'] !== '')
        {
            $this->updatePath($this->step, 'error', $result['error']);
            // Only retry transport failures before an HTTP response, not 4XX/5XX.
            if ($result['retryable'] && $this->code === 0 &&
                strtolower((string) parse_url($this->url, PHP_URL_SCHEME)) === 'https')
            {
                // Preserve the encoded path/query verbatim. Default HTTPS port
                // 443 becomes the default HTTP port; keep custom ports intact.
                $this->next = preg_replace('~^https://~i', 'http://', $this->url);
                $this->next = preg_replace('~^(http://[^/?#]+):443(?=[/?#]|$)~i', '$1', $this->next);
                $this->updatePath($this->step, 'next', $this->next);
                $this->updatePath($this->step, 'fallback', true);
                return true;
            }
            $this->setError(['type' => 'curl', 'message' => $result['error']]);
            return false;
        }

        if ($this->code >= 300 && $this->code < 400 && $result['next'] !== '')
        {
            $this->next = $result['next'];
            $this->redirect = true;
            $this->updatePath($this->step, 'next', $this->next);
        }
        elseif ($this->code < 200 || $this->code >= 300)
        {
            $this->setError(['type' => 'code', 'message' => 'URL returned a '.$this->code.' response without a redirect to follow.']);
        }

        return true;
    }

    // Keep transport separate so redirect/fallback behavior can be tested offline.
    protected function request(): array
    {
        $ch = curl_init();
        if ($ch === false) {
            return ['code' => 0, 'headers' => [], 'next' => '',
                'error' => self::ERROR_CURL_INIT, 'retryable' => false];
        }

        $headers = [];
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->url,
            CURLOPT_HTTPGET => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$headers)
            {
                $len = strlen($header);
                // Start fresh after interim responses (e.g. 100 Continue).
                if (stripos($header, 'HTTP/') === 0) {
                    $headers = [];
                }
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $headers[strtolower(trim($parts[0]))][] = trim($parts[1]);
                }
                return $len;
            }
        ]);

        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = [
            'code' => $code,
            'headers' => $headers,
            'next' => (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL),
            'error' => $response === false ? curl_error($ch) : ($code === 0 ? self::ERROR_CURL_CODE : ''),
            'retryable' => $response === false
        ];
        // PHP 8 releases the handle when its last reference goes away.
        unset($ch);
        return $result;
    }

    // get the final URL redirect
    public function getFinalRedirect(): string
    {
        $last = end($this->path);
        return $last ? $last['url'] : $this->url;
    }
}
?>