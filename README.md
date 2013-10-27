# ASEI CS - Standalone
Application Server Event Interface :: Client Side

## ASEI Standalone
The ASEI CS Standalone Edition only requires a single JavaScript file to add to your website or application. ASEI CS is the client-side portion of ASEI. Unlike it's server-side counterpart, it
can be used by itself without any other overhead code required. 

## Including the script
You will need to include your script in your html or server-side language file on your
webserver. This will be done by adding a &lt;script&gt; tag with its source being the 
location of asei.js.

If you need help with this you can [search for help](https://www.google.com/search?q=how+to+add+a+javascript+file+to+html&oq=how+to+add+a+javascript+file+to+html "Search for Help") on the internet.

## ASEI CS Script Usage
The script once included allows you to use asei as a function or an object. By default, it will
automatically initiate a connection to the relative path of ./request/ which will need handled by
your webserver in some way.

### Usage Options
There are three basic approaches to using ASEI. 
- Advanced setup
- Recommended Setup
- Basic Setup

#### Advanced Setup
The advanced setup requires that you have URL rewriting setup to redirect requests to ./request/ to
the server-side file of your choosing which should support streams. This can be done by sending 
content-type headers of text/event-stream to your client requests which keeps the connection open to 
the ASEI EventSource object.

The server-side file will need to be able to respond with a basic message formatted response.

	id: [UNIQUE ID]
	event: [EVENT NAME CLIENT IS LISTENING FOR]
	retry: [AMOUNT OF MILISECONDS TO RE-POLL]
	data: [STRING_MSG or JSON]

The advanced setup does take more work but is far more flexible, giving you the option of using whatever
server-side language you perfer.

#### Recommended Setup
The recommended setup is to use asei.js in conjunction with asei.php. Because this is the Standalone Edition of ASEI, you cannot currently use this without either getting the ASEI SC Edition or getting
just the asei.php server-side script.

This configuration handles all of the server-side work for you to make it easy to integrate in to you own
existing PHP project. It uses basic functions that mirror that of the ASEI CS to make it easy to learn
and use. With the asei.php script, you can simply use the following code to add it to your project.

	<?php include('/path/of/scripts/asei.php'); ?>

With the recommended setup you are limited to using PHP for your server-side language, but much of the overhead code does not need to be written.

#### Basic Setup
The basic setup is the easiest to use, but does also require that you have the asei.php script and use PHP as your server-side language. Because this is the Standalone Edition of ASEI, you cannot currently use this without either getting the ASEI SC Edition or getting
just the asei.php server-side script.

The best way to implement the basic setup is to get the Starter Edition which is a blank PHP website ready to go.