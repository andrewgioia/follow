<?php
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
        $request = new Follow($url);
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
    else
    {
        $error = [
            'type' => 'search',
            'message' => 'There was an issue with URL you searched. Make sure it\'s a well-formed URL.'
        ];
    }
}
?>