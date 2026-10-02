using System.Net.WebSockets;
using System.Text;

namespace Livelog.Server.Connections;

public sealed class WebsocketConnection
{
    private readonly EventHub _eventHub;

    public WebsocketConnection(EventHub eventHub)
    {
        _eventHub = eventHub;
    }

    public async Task HandleAsync(WebSocket socket, CancellationToken cancellationToken)
    {
        await using var subscription = _eventHub.Subscribe();

        var receiveTask = ReceiveAsync(socket, cancellationToken);
        var sendTask = SendAsync(socket, subscription, cancellationToken);

        await Task.WhenAny(receiveTask, sendTask);

        await CloseAsync(socket, cancellationToken);
    }

    private static async Task SendAsync(WebSocket socket, EventHub.Subscription subscription, CancellationToken cancellationToken)
    {
        await foreach (var payload in subscription.Reader.ReadAllAsync(cancellationToken))
        {
            var bytes = Encoding.UTF8.GetBytes(payload);

            await socket.SendAsync(bytes, WebSocketMessageType.Text, endOfMessage: true, cancellationToken);
        }
    }

    private static async Task ReceiveAsync(WebSocket socket, CancellationToken cancellationToken)
    {
        var buffer = new byte[1024];

        while (socket.State == WebSocketState.Open)
        {
            var result = await socket.ReceiveAsync(buffer, cancellationToken);

            if (result.MessageType == WebSocketMessageType.Close)
            {
                return;
            }
        }
    }

    private static async Task CloseAsync(WebSocket socket, CancellationToken cancellationToken)
    {
        if (socket.State is WebSocketState.Open or WebSocketState.CloseReceived)
        {
            await socket.CloseAsync(WebSocketCloseStatus.NormalClosure, "Connection closed", cancellationToken);
        }
    }
}