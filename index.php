<?php
include('inc/class.php');
include('inc/search.php');
include('inc/codes.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirect Checker</title>
    <link rel="stylesheet" type="text/css" href="inc/style.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
    <header>
        <menu>
            <h1>Follow <span>URL Redirect Checker</span></h1>
            <nav>
                <a href="/"><span>Check a new URL</span></a>
                <a href="https://git.gioia.cloud/andrew/follow" title="Gitea source code respository">
                    <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5.211 4.639c.762.06 3.164.29 7.56.391 3.118.071 5.918.069 10.15-.326a.922.922 0 0 1 1.002.811c.29 2.448-.295 4.456-.849 6.571-.393 1.499-1.148 3.433-2.093 4.91-.714 1.116-1.557 1.967-2.409 2.32a.93.93 0 0 1-.353.07h-7.307a.906.906 0 0 1-.326-.06c-1.7-.642-2.913-2.487-3.747-4.404-.771-.029-2.098-.158-3.35-.658-1.404-.562-2.697-1.574-3.19-3.31-.555-1.953-.311-3.579.511-4.685.839-1.129 2.315-1.794 4.401-1.63Zm-.466 1.823c-1.133-.032-1.976.266-2.453.908-.539.725-.58 1.801-.217 3.08.318 1.121 1.193 1.737 2.1 2.099.628.252 1.28.387 1.833.458-.672-1.892-1.118-4.479-1.263-6.545Zm3.571 7.203c.343.866.757 1.758 1.281 2.503.419.596.902 1.101 1.498 1.371h6.92c.511-.276.974-.856 1.41-1.538.844-1.318 1.512-3.046 1.863-4.383.428-1.633.906-3.19.865-4.992-2.563.215-4.61.285-6.572.286l-.026 3.061 2.092 1.012c.476.23.676.804.445 1.28l-1.977 4.088a.957.957 0 0 1-1.279.445l-4.089-1.977a.958.958 0 0 1-.445-1.279l1.977-4.089a.959.959 0 0 1 1.28-.445l.157.076c.006-.678.014-1.573.018-2.188l-1.006-.02a125.176 125.176 0 0 1-6.121-.275c.189 2.369.753 5.395 1.648 6.938.024.04.044.081.061.124v.002Zm7.733-1.407-2.495-1.199-1.2 2.495 2.496 1.199 1.199-2.495Z"/>
                    </svg>
                </a>
                <a href="https://andrewgioia.com" title="Andrew Gioia's homepage">
                    <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3.7353 17.3608H.4998L5.43 3.4998h3.891l4.9229 13.861h-3.2356l-1.06-3.1605h-5.153l-1.06 3.1605Zm1.8271-5.448h3.6262L7.431 6.6675H7.32L5.5624 11.913Zm12.7008 9.5627c-2.8871 0-4.5397-1.2586-4.8948-2.8963l2.7474-.359c.244.6297.9062 1.1847 2.217 1.1847 1.29 0 2.2317-.555 2.2317-1.983v-1.902h-.1257c-.3906.8595-1.3456 1.6923-3.0544 1.6923-2.4122 0-4.3443-1.6111-4.3443-5.0696 0-3.5396 1.9876-5.313 4.3377-5.313 1.7923 0 2.6637 1.0354 3.061 1.8818h.1116v-1.746h2.9494V17.462c0 2.66-2.1756 4.0134-5.2366 4.0134Zm.0629-6.4294c1.4292 0 2.2524-1.0827 2.2524-2.9171 0-1.8201-.8092-3.0047-2.2524-3.0047-1.4713 0-2.2525 1.2385-2.2525 3.0047 0 1.7935.7952 2.9171 2.2525 2.9171Z"/>
                    </svg>
                </a>
            </nav>
        </menu>
        <form method="post" action="/" type="application/x-www-form-urlencoded">
            <input type="url" value="<?php echo (isset($url)) ? $url : ''; ?>" placeholder="URL to check..." name="url">
            <button type="submit">Check</button>
        </form>
    </header>

<?php
// error message display
if (isset($error))
{
    echo "
    <address>
        ".$error['message']."
    </address>";
}
?>

    <main>
<?php
// if we have a valid request object
if (isset($request) && count($request->path) > 0)
{
    $steps = count($request->path) - 1;
    $count = ($steps == 1) ? 'once' : $steps.' times';
    echo "
        <article>
            <h2>Final destination</h2>
            <p>Your URL returned a <code><strong>".$request->code."</strong></code> status code and redirected ".$count.":</p>
            <p class=\"final\">
                <a href=\"".$request->getFinalRedirect()."\" target=\"blank\">
                    ".$request->getFinalRedirect()."
                </a>
            </p>
            <h3>Redirect trace</h3>
            <table>
                <thead>
                    <tr>
                        <th colspan=\"2\">Step</th>
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
        $headers = $step['headers'];
        $next = ($step['next']) ? '↩︎' : ((isset($error)) ? 'x' : '✓');
        echo "
                    <tr>
                        <td>&nbsp;</td>
                        <td><span>".$item."</span></td>
                        <td>
                            <div>".$url." <a href=\"".$url."\" target=\"blank\"><b><i>↗︎</i></b></a></div>";
        if (is_array($headers) && count($headers) > 0)
        {
            echo "
                            <input type=\"checkbox\" id=\"step-".$item."\" />
                            <section>
                                <label for=\"step-".$item."\"><i>›</i></label>";
            foreach ($headers as $hkey => $hval)
            {
                echo "
                                <hgroup>
                                    <q>".$hkey."</q><mark>".$hval[0]."</mark>
                                </hgroup>";
            }
            echo "
                            </section>";
        }
        echo "
                        </td>
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
        </article>";
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