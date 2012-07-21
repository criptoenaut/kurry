<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Your Website Name</title>

<link rel="stylesheet" href="<{$css_url}>screen.css" type="text/css" />

<script type="application/x-javascript" src="<{$js_url}>jquery-1.6.4.min.js"></script>
<script type="application/x-javascript" src="<{$js_url}>mustache.js"></script>

<{$scripts}>

</head>

<body>
<{include file="header.tpl"}>

<{include file="`$params.controller`/`$params.action`.tpl"}>

<{include file="footer.tpl"}>
</body>
</html>
