asei
====
Application Server Event Interface

# About ASEI CS
ASEI CS stands for Application Server Event Interface - Client Side. The main purpose 
is to wrap up existing functionality, but to combine similar technologies in to a 
single usable object.

## Technologies
ASEI.js uses jQuery 2.0.3 and the EventSource object to wrap up server-side events
and client-side post requests. Because the EventSource object is optimized for
server polling and controlled by the server, it deminishes the need for additional
contorollers to handle push-style notifications from server to client. In the same
regaurd, jQuery's .post() method wraps up client-side requests made to the server.

## Combined Architecture
By using these technologies together, you can facilitate both server-to-client and
client-to-server communications.



# About ASEI SS
ASEI SS stands for Application Server Event Interface - Server Side. The server side
of ASEI is written in PHP and is designed to respond to EventSource connections and
POST requests from ASEI CS. Though ASEI SS is not requied to utilize ASEI, it does
have mirroring functionality that allows a more effecient level of creation when
developing communications between server and client.

## ASEI Class Object
The ASEI SS Class is a single class and some lightweight functional code that is designed
only for use with ASEI CS. Whereas you may use ASEI CS without ASEI SS by making your own
server-side responder, you cannot however, use ASEI SS without ASEI CS. In theory is it
possible, it would be a pointless undertaking.

## ASEI Framework
With plans to someday extend the functionality in to more than a single server and single
client file; we wish to expand in to a full blown framework which operates on the basic
two-way communication platform we are building with ASEI. With much work left to be done
on the ASEI CS/SS, this is still far in the future, but one of our eventual goals.
