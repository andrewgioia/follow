<?php

// handle form submissions
if (isset($_POST['url']) || isset($_GET['url']))
{
    // get the correct url
    $url = $_POST['url'] ?? $_GET['url'];

    // check that we got a valid URL
    $url = (filter_var(trim($url, FILTER_VALIDATE_URL)))
         ? trim($url)
         : false;

    // if so, start up the redirect checks
    if ($url)
    {
        // make a request for this url and add to the path, and make sure we don't go too long
        $request = new Follow($url);
        $code = '';
        $hops = 0;
        $max = 10;

        // keep making more requests until we get a 3XX
        do {
            // set the URL
            $request->url = $url;

            // make the curl request and update the path
            $request->getHttpCode();

            // end on an error
            if ($request->error)
            {
                $error = $request->error;
                break;
            }

            // if we have a redirect to follow, update our working $url
            $url = $request->next ?? false;

            // update our code
            $code = $request->code ?? false;

            // increment our number of requests
            $hops++;

        } while ($hops < $max && $code && substr((string) $code, 0, 1) === '3'); // continue while we have a 3XX code

        // if we got a GET to automatically redirect to the url, do that now
        if (isset($_GET['go']) && $url)
        {
            header("Location: ".$url);
            exit;
        }
    }
    else
    {
        $error = [
            'type' => 'search',
            'message' => 'There was an issue with URL you searched. Make sure it\'s a well-formed URL.'
        ];
    }
}
?>