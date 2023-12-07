<?php

class RedirectCheck
{
    // constants
    const USER_AGENT = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36";
    const ERROR_CURL_INIT = "Couldn't initialize a cURL handle";
    const ERROR_CURL_CODE = "Could not curl_getinfo the HTTP code";
    const ERROR_CURL_REDIRECT = "Could not curl_getinfo the redirect URL";

    // properties
    public string $url;
    public string $next;
    public int $code;
    public int $step;
    public bool $redirect;
    public array $path;
    public array $error;

    // constructor
    public function __construct(string $url)
    {
        $this->url = $url;
        $this->next = '';
        $this->code = 0;
        $this->step = 1;
        $this->redirect = false;
        $this->error = [];
        $this->path[$this->step] = [
          'step' => $this->step,
          'url' => $this->url,
          'code' => null,
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
    public function updatePath(int $step, string $key, string $value): void
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

        // set request headers and execute
        $response = curl_setopt($ch, CURLOPT_URL, $this->url);
        $response = curl_setopt($ch, CURLOPT_HEADER, true); // enable this for debugging
        $response = curl_setopt($ch, CURLOPT_HTTPGET, true); // redundant but making sure it's a GET
        $response = curl_setopt($ch, CURLOPT_NOBODY, false); // settings this to true was returning 405s
        $response = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $response = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return output instead of going to screen
        $response = curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        $response = curl_exec($ch);

        // check for a response
        if (empty($response))
        {
            $this->setError(['type' => 'curl', 'message' => curl_error($ch)]);
            curl_close($ch);
        }
        else
        {
            // get the http status code
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE))
            {
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $this->code = ($code) ? $code : 0;
                if ($code == 200) {
                  $this->updatePath($this->step, 'code', $this->code);
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

// handle form submissions
if (isset($_POST['url']))
{
    // check that we got a valid URL
    $url = (filter_var(trim($_POST['url']), FILTER_VALIDATE_URL))
         ? trim($_POST['url'])
         : false;

    // if so, start up the redirect checks
    if ($url)
    {
        // make a request for this url and add to the path
        $request = new RedirectCheck($url);
        $code = '';

        do {
            // set the URL
            $request->url = $url;

            // make the curl request and update the path
            $request->getHttpCode();

            // end on an error
            if ($request->error)
            {
                break;
            }

            // if we have a redirect to follow, update our working $url
            $url = ($request->next) ? $request->next : false;

            // update our code
            $code = ($request->code) ? $request->code : false;

        } while ($code != 200);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Redirect Checker</title>
  <link rel="stylesheet" type="text/css" href="style.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
  <header>
    <menu>
      <h1>Redirect Checker</h1>
      <nav>
        <a href="/"><span>Check a new URL</span></a>
      </nav>
    </menu>
    <form method="post" action="index.php" type="application/x-www-form-urlencoded">
      <input type="search" value="" placeholder="URL to check..." name="url">
      <button type="submit">Check</button>
    </form>
  </header>
  <main>
<?php
if (isset($request) && count($request->path) > 0)
{
    $steps = count($request->path) - 1;
    $count = ($steps == 1) ? '1 redirect' : $steps.' redirects';
    echo "
    <article>
      <h2>
        Final destination
      </h2>
      <p>
        The URL you traced had ".$count." and ended here:
      </p>
      <p class=\"final\">
        <a href=\"".$request->getFinalRedirect()."\" target=\"blank\">
          ".$request->getFinalRedirect()."
        </a>
      </p>
      <h3>
        Redirect trace
      </h3>
      <table>
        <thead>
          <tr>
            <th>Step</th>
            <th>Request</th>
            <th>Code</th>
            <th>Redirect</th>
          </tr>
        </thead>
        <tbody>";
    foreach ($request->path as $step)
    {
      $item = $step['step'];
      $url =  $step['url'];
      $code = $step['code'];
      $next = ($step['next']) ? 'Yes' : '--';
      echo "
          <tr>
            <td>".$item."</td>
            <td>".$url."</td>
            <td><code>".$code."</code></td>
            <td>".$next."</td>
          </tr>";
    }
    echo "
        </tbody>
        <caption>
          User agent: ".$request::USER_AGENT."
        </caption>
      </table>
    </article>";
}
else
{
  echo "
    <article>
      <h2>
        Trace a URL's redirects
      </h2>
      <p>
        Enter a URL in the search box above to derive the final resolved URL after all redirects. The script runs recursive cURL requests until we get a <code>200</code> status code. This is helpful to get around link tracking or original URLs that Pi-hole outright blocks (like email links).
      </p>
      <p>
        I used to use <a href=\"https://wheregoes.com\">wheregoes.com</a> which is a good, reliable service, but decided to roll my own for privacy reasons. Absolutely nothing is logged as all URL searches are via POST and that's not currently included in my nginx logs.
      </p>
      <h3>
        Technical details
      </h3>
      <p>
        User agent
      </p>
      <p>
        Other cURL settings
      </p>
    </article>
  ";
}
?>
    <aside>
      <h3>
        HTTP response status codes
      </h3>
      <details>
        <summary>
          Informational (<code>1XX</code>&ndash;<code>199</code>)
        </summary>
        <dl>
          <dt>
            <label for="100">
              <code>100</code> Continue
            </label>
          </dt>
          <input type="checkbox" id="100">
          <dd>Continue the request or ignore the response if the request is already finished.</dd>
          <dt>
            <label for="101">
              <code>101</code> Switching Protocols
            </label>
          </dt>
          <input type="checkbox" id="101">
          <dd>Sent in response to a client's <code>Upgrade</code> request header and indicates the protocol the server is switching to.</dd>
          <dt>
            <label for="102">
              <code>102</code> Processing
            </label>
          </dt>
          <input type="checkbox" id="102">
          <dd>Server has received and is processing the request, but no response is available yet.</dd>
          <dt>
            <label for="103">
              <code>103</code> Early Hints
            </label>
          </dt>
          <input type="checkbox" id="103">
          <dd>Intended alongside <code>Link</code> header to let the user agent preconnect or start preloading resources while the server prepares a response.</dd>
        </dl>
      </details>
      <details>
        <summary>
          Successful (<code>2XX</code>&ndash;<code>299</code>)
        </summary>
        <p>
          200
        </p>
      </details>
      <details>
        <summary>
          Redirects (<code>3XX</code>&ndash;<code>399</code>)
        </summary>
        <p>
          300
        </p>
      </details>
      <details>
        <summary>
          Client error (<code>4XX</code>&ndash;<code>499</code>)
        </summary>
        <p>
          100
        </p>
      </details>
      <details>
        <summary>
          Server error (<code>5XX</code>&ndash;<code>599</code>)
        </summary>
        <p>
          500
        </p>
      </details>
      <p>
        See <a href="https://httpwg.org/specs/rfc9110.html#overview.of.status.codes">RFC 9110</a> for official documentation on each status code.
      </p>
    </aside>
  </main>
</body>
</html>