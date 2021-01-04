<?php
global $CSP;
$CSP = [
    "script-src 'self' 'unsafe-inline' 'unsafe-eval';",
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com;",
    "connect-src 'self';",
    "font-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com;",
    "child-src 'self';",
    "img-src 'self' *.openstreetmap.org data:;",
    "media-src 'self';",
    "object-src 'self';",
    // "frame-ancestors 'none';"
];

global $VPORT;
$VPORT = [
    "width=device-width,",
    "initial-scale=1.0,",
    "maximum-scale=1.0,",
    "user-scalable=0,",
    "shrink-to-fit=no"
];