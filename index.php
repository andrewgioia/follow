<?php
include('class.php');
include('search.php');
include('codes.php');
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
        <form method="post" action="/" type="application/x-www-form-urlencoded">
            <input type="url" value="" placeholder="URL to check..." name="url">
            <button type="submit">Check</button>
        </form>
    </header>
    <main>

<?php
// error message display
if (isset($error))
{

}

// if we have a valid request object
if (isset($request) && count($request->path) > 0)
{
    $steps = count($request->path) - 1;
    $count = ($steps == 1) ? '1 redirect' : $steps.' redirects';
    echo "
    <article>
        <h2>Final destination</h2>
        <p>The URL you traced had ".$count." and ended here:</p>
        <p class=\"final\">
            <a href=\"".$request->getFinalRedirect()."\" target=\"blank\">
                ".$request->getFinalRedirect()."
            </a>
        </p>
        <h3>Redirect trace</h3>
        <table>
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Request</th>
                    <th colspan=\"2\">Code</th>
                </tr>
            </thead>
            <tbody>";
    foreach ($request->path as $step)
    {
      $item = $step['step'];
      $url =  $step['url'];
      $code = $step['code'];
      $next = ($step['next']) ? '⥂⇄↩︎' : '✓';
      echo "
                <tr>
                    <td>".$item."</td>
                    <td>".$url." <a href=\"".$url."\" target=\"blank\"><b>↗︎</b></a></td>
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
            Enter a URL in the search box above to derive the final resolved URL after all redirects. The script runs recursive curl requests until we get a <code>200</code> status code. This is helpful to get around link tracking or original URLs that Pi-hole outright blocks (like email links).
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
            Other curl settings
        </p>
    </article>
  ";
}
?>
    <aside>
        <h3>
            HTTP response status codes
        </h3>
<?php
if (isset($codes) && is_array($codes) && !empty($codes))
{
    foreach ($codes as $group)
    {
        echo "
        <details>
            <summary>
                ".$group['label']." (<code>".$group['start']."</code>&ndash;<code>".$group['range']."</code>)
            </summary>
            <dl>";
        foreach ($group['codes'] as $code => $prop)
        {
            echo "
                <!-- ".$code." -->
                <dt>
                    <label for=\"".$code."\"><code>".$code."</code> ".$prop['name']."</label>
                </dt>
                <input type=\"checkbox\" id=\"".$code."\">
                <dd>".$prop['desc']."</dd>";
        }
        echo "
            </dl>
        </details>";
    }
}
?>
      <p>
        See <a href="https://httpwg.org/specs/rfc9110.html#overview.of.status.codes">RFC 9110</a> for official documentation on each status code.
      </p>
    </aside>
  </main>
</body>
</html>