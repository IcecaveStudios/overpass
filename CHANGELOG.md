# Overpass Changelog

### 2.0.3 (2019-05-13)

* **[IMPROVED]** Add "fake heartbeat" feature, to ensure services fail when the connection to RMQ is severed

### 2.0.2 (2017-03-07)

* **[FIXED]** Remove archer publish version 7.1

### 2.0.1 (2017-03-06)

* **[IMPROVED]** Update dependencies

### 2.0.0 (2017-03-06)

* **[NEW]** Amqp rpc server accepts custom error handler
* **[IMPROVED]** Include procedure name in RPC timeout exception message
* **[IMPROVED]** Include timing information for RPC calls
* **[IMPROVED]** RPC logging improvements

### 1.0.1 (2016-11-10)

* **[FIXED]** Reverted JSON logging

### 1.0.0 (2016-11-08)

* **[NEW]** Added job queing subsystem (thanks @darianbr)
* **[IMPROVED]** Use a channel-wide pre-fetch limit in the AMQP RPC server
* **[IMPROVED]** Log complete messages in JSON format, rather than a partial representation
* **[FIXED]** Allow the RPC server to crash when an internal server error (unexpected exception) occurs, this prevents applications in unrecoverable state from appearing OK while unresponsive

### 0.3.5 (2015-02-12)

* **[FIXED]** Fixed incorrect `icecave/isolator` version constraint

### 0.3.4 (2015-02-12)

* **[IMPROVED]** RPC server can now be shutdown gracefully by a signal handler

### 0.3.3 (2015-01-31)

* **[IMPROVED]** Improved logging and removed some noisy log messages

### 0.3.2 (2014-12-14)

* **[FIXED]** RPC exception messages are no longer propagated to the client unless the exception is an `ExecutionException`
* **[IMPROVED]** Improved consistency of logging

### 0.3.1 (2014-10-30)

* **[FIXED]** `AmqpRpcServer::exposeObject()` no longer exposes methods with names that begin with an underscore

### 0.3.0 (2014-10-29)

* **[BC]** Renamed `RpcClientInterface::call()` to `invokeArray()` and added `invoke($name, ...)`

### 0.2.0 (2014-10-29)

* **[NEW]** Added `RpcServerInterface::exposeObject()`

### 0.1.0 (2014-10-27)

* Initial release
