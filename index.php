<?php
include('class.php');
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
      <h1>Follow <span>URL Redirect Checker</span></h1>
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
        <dl>
          <dt>
            <label for="200">
              <code>200</code> OK
            </label>
          </dt>
          <input type="checkbox" id="200">
          <dd>The request succeeded.</dd>
          <dt>
            <label for="201">
              <code>201</code> Created
            </label>
          </dt>
          <input type="checkbox" id="201">
          <dd>The request succeeded, and a new resource was created as a result.</dd>
          <dt>
            <label for="202">
              <code>202</code> Accepted
            </label>
          </dt>
          <input type="checkbox" id="202">
          <dd>The request has been received but not yet acted upon.</dd>
          <dt>
            <label for="203">
              <code>203</code> Non-Authoritative Information
            </label>
          </dt>
          <input type="checkbox" id="203">
          <dd>Typically for mirrors of original resources, the returned metadata is not exactly the same as is available from the origin server but is collected from a local or a third-party copy.</dd>
          <dt>
            <label for="204">
              <code>204</code> No Content
            </label>
          </dt>
          <input type="checkbox" id="204">
          <dd>There is no content to send for this request, but the headers may be useful.</dd>
          <dt>
            <label for="205">
              <code>205</code> Reset Content
            </label>
          </dt>
          <input type="checkbox" id="205">
          <dd>Tells the user agent to reset the document which sent this request.</dd>
          <dt>
            <label for="206">
              <code>206</code> Partial Content
            </label>
          </dt>
          <input type="checkbox" id="206">
          <dd>Only part of a resource is sent in response to the <code>Range</code> header.</dd>
          <dt>
            <label for="207">
              <code>207</code> Multi-Status
            </label>
          </dt>
          <input type="checkbox" id="207">
          <dd>Conveys information about multiple resources, for situations where multiple status codes might be appropriate.</dd>
          <dt>
            <label for="208">
              <code>208</code> Already Reported (WebDAV)
            </label>
          </dt>
          <input type="checkbox" id="208">
          <dd>Used inside a <code>&lt;dav:propstat&gt;</code> response element to limit repetition.</dd>
          <dt>
            <label for="226">
              <code>226</code> IM Used
            </label>
          </dt>
          <input type="checkbox" id="226">
          <dd>For <code>HTTP Delta encoding</code> when the server has fullfilled a <code>GET</code> request and the response is from 1+ instance manipulations.</dd>
        </dl>
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