
# Follow

URL redirect checker. Trace the full redirect path (if one exists) for a given URL to derive that sweet final resolved endpoint. Great for getting around tracking links that might be blocked by your [network-wide ad blocker](https://pi-hole.net).

## Technical details

We're just making recursive curl requests, logging their HTTP response status code and redirect URL (if any), until we get to a `2XX` code.

There are some additional advanced curl setting in the pipeline, like changing the user agent or request method. 

## Can't I just use a curl 1-liner for this?

You sure can! You can get the final URL directly with something like this in your terminal:

    $> curl -Ls -o /dev/null -w %{url_effective} https://your-url.here

You can even bundle that up into a nice bash script or shell function. Add the `-I` flag to not download the response body and use a `HEAD` request instead, but sometimes this causes errors.

The issue here is that if you're running this command from your home network, Pi-Hole or similar will still block it. You can run this from a remote machine but sometimes I prefer a web UI, particularly when I'm on my phone.

## Issues and questions

Please submit any issues, requests, or questions at the primary repository: https://git.gioia.cloud/andrew/follow. Thanks!

## Self-hosting

Make sure PHP (ideally 8+) and a webserver that can serve PHP files (like nginx) are installed on your machine. Download or clone this repository and put it into a location accessble by your webserver (like `/var/www`). Point to `index.php` in your browser and you're good to go.

### Slightly more detailed walkthrough:

#### Install PHP-FPM, PHP's curl module, and nginx webserver:

On Debian or Ubuntu this would be:

```
$> sudo apt install php-fpm
$> sudo apt install php-curl
$> sudo apt install nginx
$> sudo apt install certbot
```

Note that this will install the latest packaged version of PHP.  At this time that's `8.1` so we'll reference that in all file paths and settings here. This will change in the future so keep that in mind.

#### Configure PHP-FPM's initialization/settings file

This is typically at `/etc/php/8.1/fpm/php.ini`. You probably don't need to change much but `max_execution_time` and enabling the log file are good ones. 

Note again that if you installed a version different from `8.1` then use that in the settings file path.

#### Create an nginx configuration file

Create a server configuration file for the website you want to access this on. We're going to install an SSL certificate so the final version below will handle PHP, so for now we just need a simple one. Save this to `/etc/nginx/sites-available/follow.conf`:

```
# pre-ssl http server
server {
    listen                  80;
    listen                  [::]:80;
    server_name             follow.yourdomain.com;

    # ssl certificate handling
    location = /.well-known/acme-challenge/ {
        root /var/www/_letsencrypt;
    }
}
```

Now create a symbolic link in nginx's `sites-enabled` directory that points to this file. This tell's nginx the configuration file is live:

`$> sudo ln -s ../sites-available/follow.yourdomain.com.conf ./follow.conf`

Test the nginx configuration, if it's ok restart nginx, and then request certbot to issue an SSL certificate for this domain and follow all of the prompts:

```
$> sudo nginx -t
$> sudo systemctl restart nginx
$> sudo certbot --nginx -d follow.yourdomain.com
```

#### Update the server configuration to handle PHP

Now that we have our SSL certificate we need to add PHP handling. I don't love the changes certbot makes to the nginx configuration file so I typically replace it with a clean one. Here's a sample final `/etc/nginx/sites-available/follow.conf` file that will handle PHP:

```
# http redirect
server {
    listen                  80;
    listen                  [::]:80;
    server_name             follow.yourdomain.com;

    # ssl references
    location = /.well-known/acme-challenge/ {
        root /var/www/_letsencrypt;
    }

    # redirect
    location / {
        return 301 https://follow.yourdomain.com$request_uri;
    }
}

# https handling
server {
    listen                  443 ssl http2;
    listen                  [::]:443 ssl http2;
    server_name             follow.yourdomain.com;
    set                     $base /var/www/follow.yourdomain.com;
    root                    $base;

    # SSL
    add_header              Strict-Transport-Security "max-age=31536000" always; # managed by Certbot
    include                 /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam             /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot
    ssl_certificate         /etc/letsencrypt/live/follow.yourdomain.com/fullchain.pem; # managed by Certbot
    ssl_certificate_key     /etc/letsencrypt/live/follow.yourdomain.com/privkey.pem; # managed by Certbot
    ssl_trusted_certificate /etc/letsencrypt/live/follow.yourdomain.com/chain.pem; # managed by Certbot
    ssl_stapling            on; # managed by Certbot
    ssl_stapling_verify     on; # managed by Certbot

    # logging
    access_log              /var/log/nginx/follow.yourdomain.com.access.log;
    error_log               /var/log/nginx/follow.yourdomain.com.error.log warn;

    # php
    index index.php index.html;
    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass    unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param   SCRIPT_FILENAME     $realpath_root$fastcgi_script_name;
        fastcgi_param   QUERY_STRING        $query_string;
        fastcgi_param   REQUEST_METHOD      $request_method;
        fastcgi_param   CONTENT_TYPE        $content_type;
        fastcgi_param   CONTENT_LENGTH      $content_length;
        fastcgi_param   SCRIPT_NAME         $fastcgi_script_name;
        fastcgi_param   REQUEST_URI         $request_uri;
        fastcgi_param   DOCUMENT_URI        $document_uri;
        fastcgi_param   DOCUMENT_ROOT       $document_root;
        fastcgi_param   SERVER_PROTOCOL     $server_protocol;
        fastcgi_param   GATEWAY_INTERFACE   CGI/1.1;
        fastcgi_param   SERVER_SOFTWARE     nginx/$nginx_version;
        fastcgi_param   REMOTE_ADDR         $remote_addr;
        fastcgi_param   REMOTE_PORT         $remote_port;
        fastcgi_param   SERVER_ADDR         $server_addr;
        fastcgi_param   SERVER_PORT         $server_port;
        fastcgi_param   SERVER_NAME         $server_name;
        fastcgi_param   HTTPS               $https if_not_empty;
        fastcgi_param   REDIRECT_STATUS     200;
        fastcgi_param   HTTP_PROXY          "";
        fastcgi_buffers  16 16k;
        fastcgi_buffer_size  32k;
    }
    location ~ /\.ht {
        deny all;
    }

    # robots
    location = /robots.txt {
        allow all;
        log_not_found off;
        access_log off;
    }

    # challenges
    location = /.well-known/acme-challenge/ {
        root /var/www/_letsencrypt;
    }
}
```

Restart nginx and PHP-FPM and nyou should be good to go:

`$> sudo systemctl restart nginx php8.1-fpm`