
(function(root) {

    var bhIndex = null;
    var rootPath = '';
    var treeHtml = '        <ul>                <li data-name="namespace:" class="opened">                    <div style="padding-left:0px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href=".html">Icecave</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:Icecave_Overpass" class="opened">                    <div style="padding-left:18px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass.html">Overpass</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:Icecave_Overpass_Amqp" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass/Amqp.html">Amqp</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:Icecave_Overpass_Amqp_PubSub" >                    <div style="padding-left:54px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass/Amqp/PubSub.html">PubSub</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:Icecave_Overpass_Amqp_PubSub_AmqpPublisher" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Amqp/PubSub/AmqpPublisher.html">AmqpPublisher</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Amqp_PubSub_AmqpSubscriber" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html">AmqpSubscriber</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Amqp_PubSub_DeclarationManager" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Amqp/PubSub/DeclarationManager.html">DeclarationManager</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:Icecave_Overpass_Amqp_Rpc" >                    <div style="padding-left:54px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass/Amqp/Rpc.html">Rpc</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:Icecave_Overpass_Amqp_Rpc_AmqpRpcClient" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html">AmqpRpcClient</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Amqp_Rpc_AmqpRpcServer" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html">AmqpRpcServer</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Amqp_Rpc_DeclarationManager" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Amqp/Rpc/DeclarationManager.html">DeclarationManager</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="class:Icecave_Overpass_Amqp_ChannelDispatcher" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="Icecave/Overpass/Amqp/ChannelDispatcher.html">ChannelDispatcher</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:Icecave_Overpass_PubSub" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass/PubSub.html">PubSub</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:Icecave_Overpass_PubSub_PublisherInterface" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="Icecave/Overpass/PubSub/PublisherInterface.html">PublisherInterface</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_PubSub_SubscriberInterface" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="Icecave/Overpass/PubSub/SubscriberInterface.html">SubscriberInterface</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:Icecave_Overpass_Rpc" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass/Rpc.html">Rpc</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:Icecave_Overpass_Rpc_Exception" >                    <div style="padding-left:54px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass/Rpc/Exception.html">Exception</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:Icecave_Overpass_Rpc_Exception_ExecutionException" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Exception/ExecutionException.html">ExecutionException</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Exception_InvalidArgumentsException" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Exception/InvalidArgumentsException.html">InvalidArgumentsException</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Exception_InvalidMessageException" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Exception/InvalidMessageException.html">InvalidMessageException</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Exception_RemoteExceptionInterface" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html">RemoteExceptionInterface</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Exception_TimeoutException" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Exception/TimeoutException.html">TimeoutException</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Exception_UnknownProcedureException" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Exception/UnknownProcedureException.html">UnknownProcedureException</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:Icecave_Overpass_Rpc_Message" >                    <div style="padding-left:54px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass/Rpc/Message.html">Message</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:Icecave_Overpass_Rpc_Message_MessageSerialization" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Message/MessageSerialization.html">MessageSerialization</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Message_MessageSerializationInterface" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html">MessageSerializationInterface</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Message_Request" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Message/Request.html">Request</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Message_Response" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Message/Response.html">Response</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Message_ResponseCode" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Message/ResponseCode.html">ResponseCode</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_Invoker" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/Invoker.html">Invoker</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_InvokerInterface" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/InvokerInterface.html">InvokerInterface</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_RpcClientInterface" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/RpcClientInterface.html">RpcClientInterface</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Rpc_RpcServerInterface" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="Icecave/Overpass/Rpc/RpcServerInterface.html">RpcServerInterface</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:Icecave_Overpass_Serialization" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass/Serialization.html">Serialization</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:Icecave_Overpass_Serialization_Exception" >                    <div style="padding-left:54px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="Icecave/Overpass/Serialization/Exception.html">Exception</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:Icecave_Overpass_Serialization_Exception_SerializationException" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="Icecave/Overpass/Serialization/Exception/SerializationException.html">SerializationException</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="class:Icecave_Overpass_Serialization_JsonSerialization" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="Icecave/Overpass/Serialization/JsonSerialization.html">JsonSerialization</a>                    </div>                </li>                            <li data-name="class:Icecave_Overpass_Serialization_SerializationInterface" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="Icecave/Overpass/Serialization/SerializationInterface.html">SerializationInterface</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="class:Icecave_Overpass_PackageInfo" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="Icecave/Overpass/PackageInfo.html">PackageInfo</a>                    </div>                </li>                </ul></div>                </li>                </ul></div>                </li>                </ul>';

    var searchTypeClasses = {
        'Namespace': 'label-default',
        'Class': 'label-info',
        'Interface': 'label-primary',
        'Trait': 'label-success',
        'Method': 'label-danger',
        '_': 'label-warning'
    };

    var searchIndex = [
                    
            {"type": "Namespace", "link": "Icecave.html", "name": "Icecave", "doc": "Namespace Icecave"},{"type": "Namespace", "link": "Icecave/Overpass.html", "name": "Icecave\\Overpass", "doc": "Namespace Icecave\\Overpass"},{"type": "Namespace", "link": "Icecave/Overpass/Amqp.html", "name": "Icecave\\Overpass\\Amqp", "doc": "Namespace Icecave\\Overpass\\Amqp"},{"type": "Namespace", "link": "Icecave/Overpass/Amqp/PubSub.html", "name": "Icecave\\Overpass\\Amqp\\PubSub", "doc": "Namespace Icecave\\Overpass\\Amqp\\PubSub"},{"type": "Namespace", "link": "Icecave/Overpass/Amqp/Rpc.html", "name": "Icecave\\Overpass\\Amqp\\Rpc", "doc": "Namespace Icecave\\Overpass\\Amqp\\Rpc"},{"type": "Namespace", "link": "Icecave/Overpass/PubSub.html", "name": "Icecave\\Overpass\\PubSub", "doc": "Namespace Icecave\\Overpass\\PubSub"},{"type": "Namespace", "link": "Icecave/Overpass/Rpc.html", "name": "Icecave\\Overpass\\Rpc", "doc": "Namespace Icecave\\Overpass\\Rpc"},{"type": "Namespace", "link": "Icecave/Overpass/Rpc/Exception.html", "name": "Icecave\\Overpass\\Rpc\\Exception", "doc": "Namespace Icecave\\Overpass\\Rpc\\Exception"},{"type": "Namespace", "link": "Icecave/Overpass/Rpc/Message.html", "name": "Icecave\\Overpass\\Rpc\\Message", "doc": "Namespace Icecave\\Overpass\\Rpc\\Message"},{"type": "Namespace", "link": "Icecave/Overpass/Serialization.html", "name": "Icecave\\Overpass\\Serialization", "doc": "Namespace Icecave\\Overpass\\Serialization"},{"type": "Namespace", "link": "Icecave/Overpass/Serialization/Exception.html", "name": "Icecave\\Overpass\\Serialization\\Exception", "doc": "Namespace Icecave\\Overpass\\Serialization\\Exception"},
            {"type": "Interface", "fromName": "Icecave\\Overpass\\PubSub", "fromLink": "Icecave/Overpass/PubSub.html", "link": "Icecave/Overpass/PubSub/PublisherInterface.html", "name": "Icecave\\Overpass\\PubSub\\PublisherInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\PubSub\\PublisherInterface", "fromLink": "Icecave/Overpass/PubSub/PublisherInterface.html", "link": "Icecave/Overpass/PubSub/PublisherInterface.html#method_publish", "name": "Icecave\\Overpass\\PubSub\\PublisherInterface::publish", "doc": "&quot;Publish a message.&quot;"},
            
            {"type": "Interface", "fromName": "Icecave\\Overpass\\PubSub", "fromLink": "Icecave/Overpass/PubSub.html", "link": "Icecave/Overpass/PubSub/SubscriberInterface.html", "name": "Icecave\\Overpass\\PubSub\\SubscriberInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\PubSub\\SubscriberInterface", "fromLink": "Icecave/Overpass/PubSub/SubscriberInterface.html", "link": "Icecave/Overpass/PubSub/SubscriberInterface.html#method_subscribe", "name": "Icecave\\Overpass\\PubSub\\SubscriberInterface::subscribe", "doc": "&quot;Subscribe to the given topic.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\PubSub\\SubscriberInterface", "fromLink": "Icecave/Overpass/PubSub/SubscriberInterface.html", "link": "Icecave/Overpass/PubSub/SubscriberInterface.html#method_unsubscribe", "name": "Icecave\\Overpass\\PubSub\\SubscriberInterface::unsubscribe", "doc": "&quot;Unsubscribe from the given topic.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\PubSub\\SubscriberInterface", "fromLink": "Icecave/Overpass/PubSub/SubscriberInterface.html", "link": "Icecave/Overpass/PubSub/SubscriberInterface.html#method_consume", "name": "Icecave\\Overpass\\PubSub\\SubscriberInterface::consume", "doc": "&quot;Consume messages from subscriptions.&quot;"},
            
            {"type": "Interface", "fromName": "Icecave\\Overpass\\Rpc\\Exception", "fromLink": "Icecave/Overpass/Rpc/Exception.html", "link": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html", "name": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface", "doc": "&quot;Interface for all RPC exceptions that can occur on the server-side.&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface", "fromLink": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html", "link": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html#method_getMessage", "name": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface::getMessage", "doc": "&quot;Get the exception message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface", "fromLink": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html", "link": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html#method_responseCode", "name": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface::responseCode", "doc": "&quot;Get the response code.&quot;"},
            
            {"type": "Interface", "fromName": "Icecave\\Overpass\\Rpc", "fromLink": "Icecave/Overpass/Rpc.html", "link": "Icecave/Overpass/Rpc/InvokerInterface.html", "name": "Icecave\\Overpass\\Rpc\\InvokerInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\InvokerInterface", "fromLink": "Icecave/Overpass/Rpc/InvokerInterface.html", "link": "Icecave/Overpass/Rpc/InvokerInterface.html#method_invoke", "name": "Icecave\\Overpass\\Rpc\\InvokerInterface::invoke", "doc": "&quot;Invoke a procedure based on a request.&quot;"},
            
            {"type": "Interface", "fromName": "Icecave\\Overpass\\Rpc\\Message", "fromLink": "Icecave/Overpass/Rpc/Message.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html#method_serializeRequest", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface::serializeRequest", "doc": "&quot;Serialize a request message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html#method_serializeResponse", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface::serializeResponse", "doc": "&quot;Serialize a response message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html#method_unserializeRequest", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface::unserializeRequest", "doc": "&quot;Unserialize a request message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html#method_unserializeResponse", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface::unserializeResponse", "doc": "&quot;Unserialize a response message.&quot;"},
            
            {"type": "Interface", "fromName": "Icecave\\Overpass\\Rpc", "fromLink": "Icecave/Overpass/Rpc.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method_invoke", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::invoke", "doc": "&quot;Invoke a remote procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method_invokeArray", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::invokeArray", "doc": "&quot;Invoke a remote procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method___call", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::__call", "doc": "&quot;Invoke a remote procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method_timeout", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::timeout", "doc": "&quot;Get the RPC response timeout.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method_setTimeout", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::setTimeout", "doc": "&quot;Set the RPC response timeout.&quot;"},
            
            {"type": "Interface", "fromName": "Icecave\\Overpass\\Rpc", "fromLink": "Icecave/Overpass/Rpc.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "fromLink": "Icecave/Overpass/Rpc/RpcServerInterface.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html#method_expose", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface::expose", "doc": "&quot;Expose a procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "fromLink": "Icecave/Overpass/Rpc/RpcServerInterface.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html#method_exposeObject", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface::exposeObject", "doc": "&quot;Expose all public methods on an object.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "fromLink": "Icecave/Overpass/Rpc/RpcServerInterface.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html#method_run", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface::run", "doc": "&quot;Run the RPC server.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "fromLink": "Icecave/Overpass/Rpc/RpcServerInterface.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html#method_stop", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface::stop", "doc": "&quot;Stop the RPC server.&quot;"},
            
            {"type": "Interface", "fromName": "Icecave\\Overpass\\Serialization", "fromLink": "Icecave/Overpass/Serialization.html", "link": "Icecave/Overpass/Serialization/SerializationInterface.html", "name": "Icecave\\Overpass\\Serialization\\SerializationInterface", "doc": "&quot;Defines a serialization protocol.&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Serialization\\SerializationInterface", "fromLink": "Icecave/Overpass/Serialization/SerializationInterface.html", "link": "Icecave/Overpass/Serialization/SerializationInterface.html#method_serialize", "name": "Icecave\\Overpass\\Serialization\\SerializationInterface::serialize", "doc": "&quot;Serialize the given payload.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Serialization\\SerializationInterface", "fromLink": "Icecave/Overpass/Serialization/SerializationInterface.html", "link": "Icecave/Overpass/Serialization/SerializationInterface.html#method_unserialize", "name": "Icecave\\Overpass\\Serialization\\SerializationInterface::unserialize", "doc": "&quot;Unserialize the given buffer.&quot;"},
            
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Amqp", "fromLink": "Icecave/Overpass/Amqp.html", "link": "Icecave/Overpass/Amqp/ChannelDispatcher.html", "name": "Icecave\\Overpass\\Amqp\\ChannelDispatcher", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\ChannelDispatcher", "fromLink": "Icecave/Overpass/Amqp/ChannelDispatcher.html", "link": "Icecave/Overpass/Amqp/ChannelDispatcher.html#method___construct", "name": "Icecave\\Overpass\\Amqp\\ChannelDispatcher::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\ChannelDispatcher", "fromLink": "Icecave/Overpass/Amqp/ChannelDispatcher.html", "link": "Icecave/Overpass/Amqp/ChannelDispatcher.html#method_wait", "name": "Icecave\\Overpass\\Amqp\\ChannelDispatcher::wait", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Amqp\\PubSub", "fromLink": "Icecave/Overpass/Amqp/PubSub.html", "link": "Icecave/Overpass/Amqp/PubSub/AmqpPublisher.html", "name": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpPublisher", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpPublisher", "fromLink": "Icecave/Overpass/Amqp/PubSub/AmqpPublisher.html", "link": "Icecave/Overpass/Amqp/PubSub/AmqpPublisher.html#method___construct", "name": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpPublisher::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpPublisher", "fromLink": "Icecave/Overpass/Amqp/PubSub/AmqpPublisher.html", "link": "Icecave/Overpass/Amqp/PubSub/AmqpPublisher.html#method_publish", "name": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpPublisher::publish", "doc": "&quot;Publish a message.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Amqp\\PubSub", "fromLink": "Icecave/Overpass/Amqp/PubSub.html", "link": "Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html", "name": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpSubscriber", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpSubscriber", "fromLink": "Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html", "link": "Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html#method___construct", "name": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpSubscriber::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpSubscriber", "fromLink": "Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html", "link": "Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html#method_subscribe", "name": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpSubscriber::subscribe", "doc": "&quot;Subscribe to the given topic.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpSubscriber", "fromLink": "Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html", "link": "Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html#method_unsubscribe", "name": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpSubscriber::unsubscribe", "doc": "&quot;Unsubscribe from the given topic.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpSubscriber", "fromLink": "Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html", "link": "Icecave/Overpass/Amqp/PubSub/AmqpSubscriber.html#method_consume", "name": "Icecave\\Overpass\\Amqp\\PubSub\\AmqpSubscriber::consume", "doc": "&quot;Consume messages from subscriptions.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Amqp\\PubSub", "fromLink": "Icecave/Overpass/Amqp/PubSub.html", "link": "Icecave/Overpass/Amqp/PubSub/DeclarationManager.html", "name": "Icecave\\Overpass\\Amqp\\PubSub\\DeclarationManager", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\PubSub\\DeclarationManager", "fromLink": "Icecave/Overpass/Amqp/PubSub/DeclarationManager.html", "link": "Icecave/Overpass/Amqp/PubSub/DeclarationManager.html#method___construct", "name": "Icecave\\Overpass\\Amqp\\PubSub\\DeclarationManager::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\PubSub\\DeclarationManager", "fromLink": "Icecave/Overpass/Amqp/PubSub/DeclarationManager.html", "link": "Icecave/Overpass/Amqp/PubSub/DeclarationManager.html#method_exchange", "name": "Icecave\\Overpass\\Amqp\\PubSub\\DeclarationManager::exchange", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\PubSub\\DeclarationManager", "fromLink": "Icecave/Overpass/Amqp/PubSub/DeclarationManager.html", "link": "Icecave/Overpass/Amqp/PubSub/DeclarationManager.html#method_queue", "name": "Icecave\\Overpass\\Amqp\\PubSub\\DeclarationManager::queue", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Amqp\\Rpc", "fromLink": "Icecave/Overpass/Amqp/Rpc.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html#method___construct", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html#method_invoke", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient::invoke", "doc": "&quot;Invoke a remote procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html#method_invokeArray", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient::invokeArray", "doc": "&quot;Invoke a remote procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html#method___call", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient::__call", "doc": "&quot;Invoke a remote procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html#method_timeout", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient::timeout", "doc": "&quot;Get the RPC response timeout.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcClient.html#method_setTimeout", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcClient::setTimeout", "doc": "&quot;Set the RPC response timeout.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Amqp\\Rpc", "fromLink": "Icecave/Overpass/Amqp/Rpc.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html#method___construct", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html#method_expose", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer::expose", "doc": "&quot;Expose a procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html#method_exposeObject", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer::exposeObject", "doc": "&quot;Expose all public methods on an object.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html#method_run", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer::run", "doc": "&quot;Run the RPC server.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer", "fromLink": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html", "link": "Icecave/Overpass/Amqp/Rpc/AmqpRpcServer.html#method_stop", "name": "Icecave\\Overpass\\Amqp\\Rpc\\AmqpRpcServer::stop", "doc": "&quot;Stop the RPC server.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Amqp\\Rpc", "fromLink": "Icecave/Overpass/Amqp/Rpc.html", "link": "Icecave/Overpass/Amqp/Rpc/DeclarationManager.html", "name": "Icecave\\Overpass\\Amqp\\Rpc\\DeclarationManager", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\DeclarationManager", "fromLink": "Icecave/Overpass/Amqp/Rpc/DeclarationManager.html", "link": "Icecave/Overpass/Amqp/Rpc/DeclarationManager.html#method___construct", "name": "Icecave\\Overpass\\Amqp\\Rpc\\DeclarationManager::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\DeclarationManager", "fromLink": "Icecave/Overpass/Amqp/Rpc/DeclarationManager.html", "link": "Icecave/Overpass/Amqp/Rpc/DeclarationManager.html#method_exchange", "name": "Icecave\\Overpass\\Amqp\\Rpc\\DeclarationManager::exchange", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\DeclarationManager", "fromLink": "Icecave/Overpass/Amqp/Rpc/DeclarationManager.html", "link": "Icecave/Overpass/Amqp/Rpc/DeclarationManager.html#method_requestQueue", "name": "Icecave\\Overpass\\Amqp\\Rpc\\DeclarationManager::requestQueue", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Amqp\\Rpc\\DeclarationManager", "fromLink": "Icecave/Overpass/Amqp/Rpc/DeclarationManager.html", "link": "Icecave/Overpass/Amqp/Rpc/DeclarationManager.html#method_responseQueue", "name": "Icecave\\Overpass\\Amqp\\Rpc\\DeclarationManager::responseQueue", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass", "fromLink": "Icecave/Overpass.html", "link": "Icecave/Overpass/PackageInfo.html", "name": "Icecave\\Overpass\\PackageInfo", "doc": "&quot;&quot;"},
                    
            {"type": "Class", "fromName": "Icecave\\Overpass\\PubSub", "fromLink": "Icecave/Overpass/PubSub.html", "link": "Icecave/Overpass/PubSub/PublisherInterface.html", "name": "Icecave\\Overpass\\PubSub\\PublisherInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\PubSub\\PublisherInterface", "fromLink": "Icecave/Overpass/PubSub/PublisherInterface.html", "link": "Icecave/Overpass/PubSub/PublisherInterface.html#method_publish", "name": "Icecave\\Overpass\\PubSub\\PublisherInterface::publish", "doc": "&quot;Publish a message.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\PubSub", "fromLink": "Icecave/Overpass/PubSub.html", "link": "Icecave/Overpass/PubSub/SubscriberInterface.html", "name": "Icecave\\Overpass\\PubSub\\SubscriberInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\PubSub\\SubscriberInterface", "fromLink": "Icecave/Overpass/PubSub/SubscriberInterface.html", "link": "Icecave/Overpass/PubSub/SubscriberInterface.html#method_subscribe", "name": "Icecave\\Overpass\\PubSub\\SubscriberInterface::subscribe", "doc": "&quot;Subscribe to the given topic.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\PubSub\\SubscriberInterface", "fromLink": "Icecave/Overpass/PubSub/SubscriberInterface.html", "link": "Icecave/Overpass/PubSub/SubscriberInterface.html#method_unsubscribe", "name": "Icecave\\Overpass\\PubSub\\SubscriberInterface::unsubscribe", "doc": "&quot;Unsubscribe from the given topic.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\PubSub\\SubscriberInterface", "fromLink": "Icecave/Overpass/PubSub/SubscriberInterface.html", "link": "Icecave/Overpass/PubSub/SubscriberInterface.html#method_consume", "name": "Icecave\\Overpass\\PubSub\\SubscriberInterface::consume", "doc": "&quot;Consume messages from subscriptions.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Exception", "fromLink": "Icecave/Overpass/Rpc/Exception.html", "link": "Icecave/Overpass/Rpc/Exception/ExecutionException.html", "name": "Icecave\\Overpass\\Rpc\\Exception\\ExecutionException", "doc": "&quot;Represents an arbitrary exception that occurred while invoking a procedure as\nopposed to an error in the call syntax or RPC system itself.&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\ExecutionException", "fromLink": "Icecave/Overpass/Rpc/Exception/ExecutionException.html", "link": "Icecave/Overpass/Rpc/Exception/ExecutionException.html#method___construct", "name": "Icecave\\Overpass\\Rpc\\Exception\\ExecutionException::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\ExecutionException", "fromLink": "Icecave/Overpass/Rpc/Exception/ExecutionException.html", "link": "Icecave/Overpass/Rpc/Exception/ExecutionException.html#method_responseCode", "name": "Icecave\\Overpass\\Rpc\\Exception\\ExecutionException::responseCode", "doc": "&quot;Get the response code.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Exception", "fromLink": "Icecave/Overpass/Rpc/Exception.html", "link": "Icecave/Overpass/Rpc/Exception/InvalidArgumentsException.html", "name": "Icecave\\Overpass\\Rpc\\Exception\\InvalidArgumentsException", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\InvalidArgumentsException", "fromLink": "Icecave/Overpass/Rpc/Exception/InvalidArgumentsException.html", "link": "Icecave/Overpass/Rpc/Exception/InvalidArgumentsException.html#method___construct", "name": "Icecave\\Overpass\\Rpc\\Exception\\InvalidArgumentsException::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\InvalidArgumentsException", "fromLink": "Icecave/Overpass/Rpc/Exception/InvalidArgumentsException.html", "link": "Icecave/Overpass/Rpc/Exception/InvalidArgumentsException.html#method_responseCode", "name": "Icecave\\Overpass\\Rpc\\Exception\\InvalidArgumentsException::responseCode", "doc": "&quot;Get the response code.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Exception", "fromLink": "Icecave/Overpass/Rpc/Exception.html", "link": "Icecave/Overpass/Rpc/Exception/InvalidMessageException.html", "name": "Icecave\\Overpass\\Rpc\\Exception\\InvalidMessageException", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\InvalidMessageException", "fromLink": "Icecave/Overpass/Rpc/Exception/InvalidMessageException.html", "link": "Icecave/Overpass/Rpc/Exception/InvalidMessageException.html#method___construct", "name": "Icecave\\Overpass\\Rpc\\Exception\\InvalidMessageException::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\InvalidMessageException", "fromLink": "Icecave/Overpass/Rpc/Exception/InvalidMessageException.html", "link": "Icecave/Overpass/Rpc/Exception/InvalidMessageException.html#method_responseCode", "name": "Icecave\\Overpass\\Rpc\\Exception\\InvalidMessageException::responseCode", "doc": "&quot;Get the response code.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Exception", "fromLink": "Icecave/Overpass/Rpc/Exception.html", "link": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html", "name": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface", "doc": "&quot;Interface for all RPC exceptions that can occur on the server-side.&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface", "fromLink": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html", "link": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html#method_getMessage", "name": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface::getMessage", "doc": "&quot;Get the exception message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface", "fromLink": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html", "link": "Icecave/Overpass/Rpc/Exception/RemoteExceptionInterface.html#method_responseCode", "name": "Icecave\\Overpass\\Rpc\\Exception\\RemoteExceptionInterface::responseCode", "doc": "&quot;Get the response code.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Exception", "fromLink": "Icecave/Overpass/Rpc/Exception.html", "link": "Icecave/Overpass/Rpc/Exception/TimeoutException.html", "name": "Icecave\\Overpass\\Rpc\\Exception\\TimeoutException", "doc": "&quot;Indicates that a timeout has occurred.&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\TimeoutException", "fromLink": "Icecave/Overpass/Rpc/Exception/TimeoutException.html", "link": "Icecave/Overpass/Rpc/Exception/TimeoutException.html#method___construct", "name": "Icecave\\Overpass\\Rpc\\Exception\\TimeoutException::__construct", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Exception", "fromLink": "Icecave/Overpass/Rpc/Exception.html", "link": "Icecave/Overpass/Rpc/Exception/UnknownProcedureException.html", "name": "Icecave\\Overpass\\Rpc\\Exception\\UnknownProcedureException", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\UnknownProcedureException", "fromLink": "Icecave/Overpass/Rpc/Exception/UnknownProcedureException.html", "link": "Icecave/Overpass/Rpc/Exception/UnknownProcedureException.html#method___construct", "name": "Icecave\\Overpass\\Rpc\\Exception\\UnknownProcedureException::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Exception\\UnknownProcedureException", "fromLink": "Icecave/Overpass/Rpc/Exception/UnknownProcedureException.html", "link": "Icecave/Overpass/Rpc/Exception/UnknownProcedureException.html#method_responseCode", "name": "Icecave\\Overpass\\Rpc\\Exception\\UnknownProcedureException::responseCode", "doc": "&quot;Get the response code.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc", "fromLink": "Icecave/Overpass/Rpc.html", "link": "Icecave/Overpass/Rpc/Invoker.html", "name": "Icecave\\Overpass\\Rpc\\Invoker", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Invoker", "fromLink": "Icecave/Overpass/Rpc/Invoker.html", "link": "Icecave/Overpass/Rpc/Invoker.html#method_invoke", "name": "Icecave\\Overpass\\Rpc\\Invoker::invoke", "doc": "&quot;Invoke a procedure based on a request.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc", "fromLink": "Icecave/Overpass/Rpc.html", "link": "Icecave/Overpass/Rpc/InvokerInterface.html", "name": "Icecave\\Overpass\\Rpc\\InvokerInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\InvokerInterface", "fromLink": "Icecave/Overpass/Rpc/InvokerInterface.html", "link": "Icecave/Overpass/Rpc/InvokerInterface.html#method_invoke", "name": "Icecave\\Overpass\\Rpc\\InvokerInterface::invoke", "doc": "&quot;Invoke a procedure based on a request.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Message", "fromLink": "Icecave/Overpass/Rpc/Message.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerialization.html", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerialization.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerialization.html#method___construct", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerialization.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerialization.html#method_serializeRequest", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization::serializeRequest", "doc": "&quot;Serialize a request message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerialization.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerialization.html#method_serializeResponse", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization::serializeResponse", "doc": "&quot;Serialize a response message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerialization.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerialization.html#method_unserializeRequest", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization::unserializeRequest", "doc": "&quot;Unserialize a request message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerialization.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerialization.html#method_unserializeResponse", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerialization::unserializeResponse", "doc": "&quot;Unserialize a response message.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Message", "fromLink": "Icecave/Overpass/Rpc/Message.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html#method_serializeRequest", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface::serializeRequest", "doc": "&quot;Serialize a request message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html#method_serializeResponse", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface::serializeResponse", "doc": "&quot;Serialize a response message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html#method_unserializeRequest", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface::unserializeRequest", "doc": "&quot;Unserialize a request message.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface", "fromLink": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html", "link": "Icecave/Overpass/Rpc/Message/MessageSerializationInterface.html#method_unserializeResponse", "name": "Icecave\\Overpass\\Rpc\\Message\\MessageSerializationInterface::unserializeResponse", "doc": "&quot;Unserialize a response message.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Message", "fromLink": "Icecave/Overpass/Rpc/Message.html", "link": "Icecave/Overpass/Rpc/Message/Request.html", "name": "Icecave\\Overpass\\Rpc\\Message\\Request", "doc": "&quot;Represents an RPC request.&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Request", "fromLink": "Icecave/Overpass/Rpc/Message/Request.html", "link": "Icecave/Overpass/Rpc/Message/Request.html#method_create", "name": "Icecave\\Overpass\\Rpc\\Message\\Request::create", "doc": "&quot;Create a request.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Request", "fromLink": "Icecave/Overpass/Rpc/Message/Request.html", "link": "Icecave/Overpass/Rpc/Message/Request.html#method_name", "name": "Icecave\\Overpass\\Rpc\\Message\\Request::name", "doc": "&quot;Get the name of the procedure to call.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Request", "fromLink": "Icecave/Overpass/Rpc/Message/Request.html", "link": "Icecave/Overpass/Rpc/Message/Request.html#method_arguments", "name": "Icecave\\Overpass\\Rpc\\Message\\Request::arguments", "doc": "&quot;Get the arguments to pass.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Request", "fromLink": "Icecave/Overpass/Rpc/Message/Request.html", "link": "Icecave/Overpass/Rpc/Message/Request.html#method___toString", "name": "Icecave\\Overpass\\Rpc\\Message\\Request::__toString", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Message", "fromLink": "Icecave/Overpass/Rpc/Message.html", "link": "Icecave/Overpass/Rpc/Message/Response.html", "name": "Icecave\\Overpass\\Rpc\\Message\\Response", "doc": "&quot;Represents an RPC response.&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Response", "fromLink": "Icecave/Overpass/Rpc/Message/Response.html", "link": "Icecave/Overpass/Rpc/Message/Response.html#method_create", "name": "Icecave\\Overpass\\Rpc\\Message\\Response::create", "doc": "&quot;Create a response.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Response", "fromLink": "Icecave/Overpass/Rpc/Message/Response.html", "link": "Icecave/Overpass/Rpc/Message/Response.html#method_createFromValue", "name": "Icecave\\Overpass\\Rpc\\Message\\Response::createFromValue", "doc": "&quot;Create a success response.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Response", "fromLink": "Icecave/Overpass/Rpc/Message/Response.html", "link": "Icecave/Overpass/Rpc/Message/Response.html#method_createFromException", "name": "Icecave\\Overpass\\Rpc\\Message\\Response::createFromException", "doc": "&quot;Create a failure response.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Response", "fromLink": "Icecave/Overpass/Rpc/Message/Response.html", "link": "Icecave/Overpass/Rpc/Message/Response.html#method_code", "name": "Icecave\\Overpass\\Rpc\\Message\\Response::code", "doc": "&quot;Get the response code.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Response", "fromLink": "Icecave/Overpass/Rpc/Message/Response.html", "link": "Icecave/Overpass/Rpc/Message/Response.html#method_value", "name": "Icecave\\Overpass\\Rpc\\Message\\Response::value", "doc": "&quot;Get the response value.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Response", "fromLink": "Icecave/Overpass/Rpc/Message/Response.html", "link": "Icecave/Overpass/Rpc/Message/Response.html#method_extract", "name": "Icecave\\Overpass\\Rpc\\Message\\Response::extract", "doc": "&quot;Extract the return value or exception.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\Message\\Response", "fromLink": "Icecave/Overpass/Rpc/Message/Response.html", "link": "Icecave/Overpass/Rpc/Message/Response.html#method___toString", "name": "Icecave\\Overpass\\Rpc\\Message\\Response::__toString", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc\\Message", "fromLink": "Icecave/Overpass/Rpc/Message.html", "link": "Icecave/Overpass/Rpc/Message/ResponseCode.html", "name": "Icecave\\Overpass\\Rpc\\Message\\ResponseCode", "doc": "&quot;&quot;"},
                    
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc", "fromLink": "Icecave/Overpass/Rpc.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method_invoke", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::invoke", "doc": "&quot;Invoke a remote procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method_invokeArray", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::invokeArray", "doc": "&quot;Invoke a remote procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method___call", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::__call", "doc": "&quot;Invoke a remote procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method_timeout", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::timeout", "doc": "&quot;Get the RPC response timeout.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcClientInterface", "fromLink": "Icecave/Overpass/Rpc/RpcClientInterface.html", "link": "Icecave/Overpass/Rpc/RpcClientInterface.html#method_setTimeout", "name": "Icecave\\Overpass\\Rpc\\RpcClientInterface::setTimeout", "doc": "&quot;Set the RPC response timeout.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Rpc", "fromLink": "Icecave/Overpass/Rpc.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "fromLink": "Icecave/Overpass/Rpc/RpcServerInterface.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html#method_expose", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface::expose", "doc": "&quot;Expose a procedure.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "fromLink": "Icecave/Overpass/Rpc/RpcServerInterface.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html#method_exposeObject", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface::exposeObject", "doc": "&quot;Expose all public methods on an object.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "fromLink": "Icecave/Overpass/Rpc/RpcServerInterface.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html#method_run", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface::run", "doc": "&quot;Run the RPC server.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Rpc\\RpcServerInterface", "fromLink": "Icecave/Overpass/Rpc/RpcServerInterface.html", "link": "Icecave/Overpass/Rpc/RpcServerInterface.html#method_stop", "name": "Icecave\\Overpass\\Rpc\\RpcServerInterface::stop", "doc": "&quot;Stop the RPC server.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Serialization\\Exception", "fromLink": "Icecave/Overpass/Serialization/Exception.html", "link": "Icecave/Overpass/Serialization/Exception/SerializationException.html", "name": "Icecave\\Overpass\\Serialization\\Exception\\SerializationException", "doc": "&quot;&quot;"},
                    
            {"type": "Class", "fromName": "Icecave\\Overpass\\Serialization", "fromLink": "Icecave/Overpass/Serialization.html", "link": "Icecave/Overpass/Serialization/JsonSerialization.html", "name": "Icecave\\Overpass\\Serialization\\JsonSerialization", "doc": "&quot;JSON serialization protocol.&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Serialization\\JsonSerialization", "fromLink": "Icecave/Overpass/Serialization/JsonSerialization.html", "link": "Icecave/Overpass/Serialization/JsonSerialization.html#method_serialize", "name": "Icecave\\Overpass\\Serialization\\JsonSerialization::serialize", "doc": "&quot;Serialize the given payload.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Serialization\\JsonSerialization", "fromLink": "Icecave/Overpass/Serialization/JsonSerialization.html", "link": "Icecave/Overpass/Serialization/JsonSerialization.html#method_unserialize", "name": "Icecave\\Overpass\\Serialization\\JsonSerialization::unserialize", "doc": "&quot;Unserialize the given buffer.&quot;"},
            
            {"type": "Class", "fromName": "Icecave\\Overpass\\Serialization", "fromLink": "Icecave/Overpass/Serialization.html", "link": "Icecave/Overpass/Serialization/SerializationInterface.html", "name": "Icecave\\Overpass\\Serialization\\SerializationInterface", "doc": "&quot;Defines a serialization protocol.&quot;"},
                                                        {"type": "Method", "fromName": "Icecave\\Overpass\\Serialization\\SerializationInterface", "fromLink": "Icecave/Overpass/Serialization/SerializationInterface.html", "link": "Icecave/Overpass/Serialization/SerializationInterface.html#method_serialize", "name": "Icecave\\Overpass\\Serialization\\SerializationInterface::serialize", "doc": "&quot;Serialize the given payload.&quot;"},
                    {"type": "Method", "fromName": "Icecave\\Overpass\\Serialization\\SerializationInterface", "fromLink": "Icecave/Overpass/Serialization/SerializationInterface.html", "link": "Icecave/Overpass/Serialization/SerializationInterface.html#method_unserialize", "name": "Icecave\\Overpass\\Serialization\\SerializationInterface::unserialize", "doc": "&quot;Unserialize the given buffer.&quot;"},
            
            
                                        // Fix trailing commas in the index
        {}
    ];

    /** Tokenizes strings by namespaces and functions */
    function tokenizer(term) {
        if (!term) {
            return [];
        }

        var tokens = [term];
        var meth = term.indexOf('::');

        // Split tokens into methods if "::" is found.
        if (meth > -1) {
            tokens.push(term.substr(meth + 2));
            term = term.substr(0, meth - 2);
        }

        // Split by namespace or fake namespace.
        if (term.indexOf('\\') > -1) {
            tokens = tokens.concat(term.split('\\'));
        } else if (term.indexOf('_') > 0) {
            tokens = tokens.concat(term.split('_'));
        }

        // Merge in splitting the string by case and return
        tokens = tokens.concat(term.match(/(([A-Z]?[^A-Z]*)|([a-z]?[^a-z]*))/g).slice(0,-1));

        return tokens;
    };

    root.Sami = {
        /**
         * Cleans the provided term. If no term is provided, then one is
         * grabbed from the query string "search" parameter.
         */
        cleanSearchTerm: function(term) {
            // Grab from the query string
            if (typeof term === 'undefined') {
                var name = 'search';
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
                var results = regex.exec(location.search);
                if (results === null) {
                    return null;
                }
                term = decodeURIComponent(results[1].replace(/\+/g, " "));
            }

            return term.replace(/<(?:.|\n)*?>/gm, '');
        },

        /** Searches through the index for a given term */
        search: function(term) {
            // Create a new search index if needed
            if (!bhIndex) {
                bhIndex = new Bloodhound({
                    limit: 500,
                    local: searchIndex,
                    datumTokenizer: function (d) {
                        return tokenizer(d.name);
                    },
                    queryTokenizer: Bloodhound.tokenizers.whitespace
                });
                bhIndex.initialize();
            }

            results = [];
            bhIndex.get(term, function(matches) {
                results = matches;
            });

            if (!rootPath) {
                return results;
            }

            // Fix the element links based on the current page depth.
            return $.map(results, function(ele) {
                if (ele.link.indexOf('..') > -1) {
                    return ele;
                }
                ele.link = rootPath + ele.link;
                if (ele.fromLink) {
                    ele.fromLink = rootPath + ele.fromLink;
                }
                return ele;
            });
        },

        /** Get a search class for a specific type */
        getSearchClass: function(type) {
            return searchTypeClasses[type] || searchTypeClasses['_'];
        },

        /** Add the left-nav tree to the site */
        injectApiTree: function(ele) {
            ele.html(treeHtml);
        }
    };

    $(function() {
        // Modify the HTML to work correctly based on the current depth
        rootPath = $('body').attr('data-root-path');
        treeHtml = treeHtml.replace(/href="/g, 'href="' + rootPath);
        Sami.injectApiTree($('#api-tree'));
    });

    return root.Sami;
})(window);

$(function() {

    // Enable the version switcher
    $('#version-switcher').change(function() {
        window.location = $(this).val()
    });

    
        // Toggle left-nav divs on click
        $('#api-tree .hd span').click(function() {
            $(this).parent().parent().toggleClass('opened');
        });

        // Expand the parent namespaces of the current page.
        var expected = $('body').attr('data-name');

        if (expected) {
            // Open the currently selected node and its parents.
            var container = $('#api-tree');
            var node = $('#api-tree li[data-name="' + expected + '"]');
            // Node might not be found when simulating namespaces
            if (node.length > 0) {
                node.addClass('active').addClass('opened');
                node.parents('li').addClass('opened');
                var scrollPos = node.offset().top - container.offset().top + container.scrollTop();
                // Position the item nearer to the top of the screen.
                scrollPos -= 200;
                container.scrollTop(scrollPos);
            }
        }

    
    
        var form = $('#search-form .typeahead');
        form.typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, {
            name: 'search',
            displayKey: 'name',
            source: function (q, cb) {
                cb(Sami.search(q));
            }
        });

        // The selection is direct-linked when the user selects a suggestion.
        form.on('typeahead:selected', function(e, suggestion) {
            window.location = suggestion.link;
        });

        // The form is submitted when the user hits enter.
        form.keypress(function (e) {
            if (e.which == 13) {
                $('#search-form').submit();
                return true;
            }
        });

    
});


