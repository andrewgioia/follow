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
        $this->path[$this->step] = [
          'step' => $this->step,
          'url' => $this->url,
          'code' => null,
          'headers' => [],
          'next' => null ];
    }

    // create an error message
    private function setError(array $error): void
    {
        $this->error = $error;
    }

    // add an entry to the URL path
    private function addPath(int $step, array $path): void
    {
        $this->path[$step] = $path;
    }

    // update a path entry
    public function updatePath(int $step, string $key, $value): void
    {
        $this->path[$step][$key] = $value;
    }

    // create a curl request for a URL
    public function getHttpCode()
    {
        // initate curl request
        $ch = curl_init();
        if (!$ch) {
            $this->setError = ['type' => 'curl', 'message' => self::ERROR_CURL_INIT];
            return false;
        }

        // set request header options
        $response = curl_setopt($ch, CURLOPT_URL, $this->url);
        $response = curl_setopt($ch, CURLOPT_HEADER, true); // enable this for debugging
        $response = curl_setopt($ch, CURLOPT_HTTPGET, true); // redundant but making sure it's a GET
        $response = curl_setopt($ch, CURLOPT_NOBODY, false); // settings this to true was returning 405s
        $response = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $response = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return output instead of going to screen
        $response = curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);

        // save response header to $header variable
        $headers = [];
        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$headers)
            {
                $len = strlen($header);
                $header = explode(':', $header, 2);

                // ignore invalid headers
                if (count($header) < 2) {
                    return $len;
                }

                // save the header formatted
                $headers[strtolower(trim($header[0]))][] = trim($header[1]);

                // return the header length
                return $len;
            }
        );

        // execute the curl handle
        $response = curl_exec($ch);

        // check for a response
        if (empty($response))
        {
            $this->setError(['type' => 'curl', 'message' => curl_error($ch)]);
            curl_close($ch);
        }
        else
        {
            // save the response headers to the path entry
            $this->headers = $headers;
            $this->updatePath($this->step, 'headers', $headers);

            // get the http status code
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE))
            {
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $this->code = ($code) ? $code : 0;

                // handle certain codes specifically
                switch ($code)
                {
                    case 200:
                        $this->updatePath($this->step, 'code', $this->code);
                        return true;
                    case 404:
                        $this->updatePath($this->step, 'code', $this->code);
                        $this->setError(['type' => 'code', 'message' => 'URL returned a 404 error response.']);
                        return true;
                }
            }
            else
            {
                $this->code = 0;
                $this->setError(['type' => 'curl', 'message' => self::ERROR_CURL_CODE]);
                return false;
            }

            // get any redirect url to follow next
            if (curl_getinfo($ch, CURLINFO_REDIRECT_URL))
            {
                $next = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
                $this->redirect = ($next) ? true : false;
                $this->next = ($next) ? $next : '';
            }
            else
            {
                $this->redirect = false;
                $this->next = '';
            }

            // close the session
            curl_close($ch);

            // update the current path
            $this->updatePath($this->step, 'code', $this->code);
            $this->updatePath($this->step, 'next', $this->next);

            // start the next path
            $this->step++;
            $this->addPath(
              $this->step,
              [
                'step' => $this->step,
                'url' => $this->next,
                'code' => null,
                'header' => '',
                'next' => null
              ]);

            return true;
        }

        // return false if we get here
        return false;
    }

    // get the final URL redirect
    public function getFinalRedirect(): string
    {
        $last = end($this->path);
        return $last['url'];
    }
}
?>