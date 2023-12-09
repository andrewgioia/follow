<?php
$codes = [
    100 => [
        'label' => 'Informational',
        'start' => '1XX',
        'range' => '199',
        'codes' => [
            100 => [
                'name' => 'Continue',
                'desc' => 'Continue the request or ignore the response if the request is already finished.'
            ],
            101 => [
                'name' => 'Switching Protocols',
                'desc' => 'Sent in response to a client\'s <code>Upgrade</code> request header and indicates the protocol the server is switching to.'
            ],
            102 => [
                'name' => 'Processing',
                'desc' => 'Server has received and is processing the request, but no response is available yet.'
            ],
            103 => [
                'name' => 'Early Hints',
                'desc' => 'Intended alongside <code>Link</code> header to let the user agent preconnect or start preloading resources while the server prepares a response.'
            ]
        ]
    ],
    200 => [
        'label' => 'Successful',
        'start' => '2XX',
        'range' => '299',
        'codes' => [
            200 => [
                'name' => 'OK',
                'desc' => 'The request succeeded.'
            ],
            201 => [
                'name' => 'Created',
                'desc' => 'The request succeeded, and a new resource was created as a result.'
            ],
            202 => [
                'name' => 'Accepted',
                'desc' => 'The request has been received but not yet acted upon.'
            ],
            203 => [
                'name' => 'Non-Authoritative Information',
                'desc' => 'Typically for mirrors of original resources, the returned metadata is not exactly the same as is available from the origin server but is collected from a local or a third-party copy.'
            ],
            204 => [
                'name' => 'No Content',
                'desc' => 'There is no content to send for this request, but the headers may be useful.'
            ],
            205 => [
                'name' => 'Reset Content',
                'desc' => 'Tells the user agent to reset the document which sent this request.'
            ],
            206 => [
                'name' => 'Partial Content',
                'desc' => 'Only part of a resource is sent in response to the <code>Range</code> header.'
            ],
            207 => [
                'name' => 'Multi-Status',
                'desc' => 'Conveys information about multiple resources, for situations where multiple status codes might be appropriate.'
            ],
            208 => [
                'name' => 'Already Reported (WebDAV)',
                'desc' => 'Used inside a <code>&lt;dav:propstat&gt;</code> response element to limit repetition.'
            ],
            226 => [
                'name' => 'IM Used',
                'desc' => 'For <code>HTTP Delta encoding</code> when the server has fullfilled a <code>GET</code> request and the response is from 1+ instance manipulations.'
            ]
        ]
    ],
    300 => [
        'label' => 'Redirection',
        'start' => '3XX',
        'range' => '399',
        'codes' => [
            300 => [
                'name' => 'Multiple Choices',
                'desc' => 'The request has more than one possible response and the user agent or user should choose one of them.'
            ],
            301 => [
                'name' => 'Moved Permanently',
                'desc' => 'The URL of the requested resource has been changed permanently. The new URL is given in the response.'
            ],
            302 => [
                'name' => 'Found',
                'desc' => 'URI of the requested resource has been changed temporarily and may change in the future.'
            ],
            303 => [
                'name' => 'See Other',
                'desc' => 'Client should get the requested resource at another URI with a GET request.'
            ],
            304 => [
                'name' => 'Not Modified',
                'desc' => 'For caching purposes, indicating the response has not been modified so the client can use the cached version.'
            ],
            305 => [
                'name' => '<del>Use Proxy</del>',
                'desc' => 'Deprecated. Indicates the requested response must be accessed via proxy.'
            ],
            306 => [
                'name' => '(Reserved)',
                'desc' => 'Unused and now reserved. Previously in the HTTP/1.1 spec.'
            ],
            307 => [
                'name' => 'Temporary Redirect',
                'desc' => 'Client should get the requested resource at another URI via the same method. Similar to <code>302</code> but the request method must stay the same.'
            ],
            308 => [
                'name' => 'Permanent Redirect',
                'desc' => 'Response is now permanently located at a new URI included in the <code>Location:</code> header. Similar to <code>301</code> but the request method must stay the same.'
            ]
        ]
    ],
    400 => [
        'label' => 'Client Error',
        'start' => '4XX',
        'range' => '499',
        'codes' => [
            400 => [
                'name' => 'Bad Request',
                'desc' => 'Server will not process the request due to an unspecified error by the client.'
            ],
            401 => [
                'name' => 'Unauthorized',
                'desc' => 'More accurately, "unauthenticated."'
            ],
            402 => [
                'name' => '<var>Payment Required</var>',
                'desc' => 'Reserved for future use in digital payment systems.'
            ],
            403 => [
                'name' => 'Forbidden',
                'desc' => 'Client is unauthorized and does not have access rights to the resource.'
            ],
            404 => [
                'name' => 'Not Found',
                'desc' => 'Server cannot find the requested resource. One of the most common response headers after <code>200</code>.'
            ],
            405 => [
                'name' => 'Method Not Allowed',
                'desc' => 'Request method is known by the server but is not supported by the target resource. Common in APIs.'
            ],
            406 => [
                'name' => 'Not Acceptable',
                'desc' => 'Server performs content negotiation with the client and does not find content that meets the user agent\'s criteria.'
            ],
            407 => [
                'name' => 'Proxy Authentication Required',
                'desc' => 'Similar to <code>401 Unauthorized</code> but authentication is needed to be done by a proxy.'
            ],
            408 => [
                'name' => 'Request Timeout',
                'desc' => 'Server shuts down an unnused connection by the client.'
            ],
            409 => [
                'name' => 'Conflict',
                'desc' => 'Request conflicts with the current state of the server.'
            ],
            410 => [
                'name' => 'Gone',
                'desc' => 'Requested resource has been permanently deleted from server, with no forwarding address.'
            ],
            411 => [
                'name' => 'Length Required',
                'desc' => 'Server rejected the request because the <code>Content-Length</code> header field is not defined and the server requires it.'
            ],
            412 => [
                'name' => 'Precondition Failed',
                'desc' => 'Client has indicated preconditions in its headers which the server does not meet.'
            ],
            413 => [
                'name' => 'Payload Too Large',
                'desc' => 'Request entity is larger than limits defined by server.'
            ],
            414 => [
                'name' => 'URI Too Long',
                'desc' => 'URI requested by the client is longer than the server is willing to interpret.'
            ],
            415 => [
                'name' => 'Unsupported Media Type',
                'desc' => 'Media format of the requested data is not supported by the server.'
            ],
            416 => [
                'name' => 'Range Not Satisfiable',
                'desc' => 'Size specified by the <code>Range</code> header field in the request cannot be fulfilled.'
            ],
            417 => [
                'name' => 'Expectation Failed',
                'desc' => 'Expectation indicated by the <code>Expect</code> request header field cannot be met by the server.'
            ],
            418 => [
                'name' => 'I\'m a teapot',
                'desc' => 'Server refuses the attempt to brew coffee with a teapot. Reference to Hyper Text Coffee Pot Control Protocol defined in April Fools\' jokes in 1998 and 2014!'
            ],
            421 => [
                'name' => 'Misdirected Request',
                'desc' => 'Request was directed at a server that is not able to produce a response.'
            ],
            422 => [
                'name' => 'Unprocessable Content (WebDAV)',
                'desc' => 'Request was well-formed but was unable to be followed due to semantic errors.'
            ],
            423 => [
                'name' => 'Locked (WebDAV)',
                'desc' => 'Resource that is being accessed is locked.'
            ],
            424 => [
                'name' => 'Failed Dependency (WebDAV)',
                'desc' => 'Request failed due to failure of a previous request.'
            ],
            425 => [
                'name' => '<var>Too Early</var>',
                'desc' => 'Reserved for future use. Server is unwilling to risk processing a request that might be replayed.'
            ],
            426 => [
                'name' => 'Upgrade Required',
                'desc' => 'Request currently refused over the current protocol but the server may provide a reponse should the client use a different protocol.'
            ],
            428 => [
                'name' => 'Precondition Required',
                'desc' => 'Origin server requires the request to be conditional.'
            ],
            429 => [
                'name' => 'Too Many Requests',
                'desc' => 'User agent has sent too many requests in a given amount of time (i.e., it\'s rate limited).'
            ],
            431 => [
                'name' => 'Request Header Fields Too Large',
                'desc' => 'Server is unwilling to process the request because its header fields are too large.'
            ],
            451 => [
                'name' => 'Unavailable For Legal Reasons',
                'desc' => 'User agent requested a resource that cannot legally be provided (e.g., web page censored by a government).'
            ]
        ]
    ],
    500 => [
        'label' => 'Server Error',
        'start' => '5XX',
        'range' => '599',
        'codes' => [
            500 => [
                'name' => 'Internal Server Error',
                'desc' => 'Server has encountered a situation it does not know how to handle. One of the most common response codes.'
            ],
            501 => [
                'name' => 'Not Implemented',
                'desc' => 'Request method is not supported by the server and cannot be handled.'
            ],
            502 => [
                'name' => 'Bad Gateway',
                'desc' => 'Server recieved an invalid response while acting as a gateway.'
            ],
            503 => [
                'name' => 'Service Unavailable',
                'desc' => 'Server is not ready to handle the request, commonly due to maintenance downtime or overload.'
            ],
            504 => [
                'name' => 'Gateway Timeout',
                'desc' => 'Server cannot get a response in time while acting as a gateway.'
            ],
            505 => [
                'name' => 'HTTP Version Not Supported',
                'desc' => 'HTTP version used in the request is not supported by the server.'
            ],
            506 => [
                'name' => 'Variant Also Negotiates',
                'desc' => 'Only in Transparent Content Negotiation contexts; server has an internal configuration error where the user agent\'s chosen variant is not available.'
            ],
            507 => [
                'name' => 'Insufficient Storage (WebDAV)',
                'desc' => 'Server is unable to store the representation needed to successfully complete the request.'
            ],
            508 => [
                'name' => 'Loop Detected (WebDAV)',
                'desc' => 'Server detected an infinite loop while processing the request.'
            ],
            509 => [
                'name' => 'Not Extended',
                'desc' => 'Further extensions to the request are required for the server to fulfill it.'
            ],
            510 => [
                'name' => 'Network Authentication Required',
                'desc' => 'Client needs to authenticate to gain network access.'
            ]
        ]
    ]
];
?>