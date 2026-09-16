<?php

// handle form submissions
if (isset($_POST['url']) || isset($_GET['url']))
{
    $url = $_POST['url'] ?? $_GET['url'];
    $url = is_string($url) ? trim($url) : '';
    $valid = filter_var($url, FILTER_VALIDATE_URL) &&
        in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);

    if ($valid)
    {
        $request = new Follow($url);
        $max = 10;
        $visited = [];

        for ($hops = 0; $hops < $max; $hops++)
        {
            $visited[$request->url] = true;
            $request->getHttpCode();
            if ($request->error || $request->next === '') {
                break;
            }
            if (isset($visited[$request->next])) {
                $request->error = ['type' => 'redirect', 'message' => 'Redirect loop detected (including HTTP fallbacks).'];
                break;
            }
            if ($hops === $max - 1) {
                $request->error = ['type' => 'redirect', 'message' => 'Stopped after '.$max.' requests (including HTTP fallbacks).'];
                break;
            }
            $request->url = $request->next;
        }

        if ($request->error) {
            $error = $request->error;
        }

        // Only send the visitor to a successfully resolved destination.
        if (isset($_GET['go']) && !$request->error && $request->code >= 200 && $request->code < 300)
        {
            header('Location: '.$request->getFinalRedirect());
            exit;
        }
    }
    else
    {
        $error = [
            'type' => 'search',
            'message' => 'There was an issue with the URL you searched. Use a well-formed HTTP or HTTPS URL.'
        ];
    }
}
?>