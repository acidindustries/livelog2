using System.Collections.Concurrent;
using System.Text.Json;
using System.Text.Json.Serialization;
using System.Threading.Channels;
using Livelog.Server.Ingestion.Events;

namespace Livelog.Server.Connections;

public sealed class EventHub
{
    private const int ClientQueueCapacity = 256;
    private readonly ConcurrentDictionary<Guid, Channel<string>> _clients = new();
    private readonly JsonSerializerOptions _serializerOptions = new JsonSerializerOptions
    {
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase,
        Converters =
        {
            new JsonStringEnumConverter()
        }
    };

    private long _eventsPublished;

    public int ConnectionCount => _clients.Count;
    public long EventsReceived => Interlocked.Read(ref _eventsPublished);

    public Subscription Subscribe()
    {
        var id = Guid.NewGuid();
        var channel = Channel.CreateBounded<string>(new BoundedChannelOptions(ClientQueueCapacity)
        {
            FullMode = BoundedChannelFullMode.DropOldest,
            SingleReader = true,
            SingleWriter = false
        });

        _clients[id] = channel;
        return new Subscription(this, id, channel.Reader);
    }

    public void Publish(LivelogEvent @event)
    {
        var payload = JsonSerializer.Serialize<LivelogEvent>(@event, _serializerOptions);

        Publish(payload);
    }

    public void Publish(string payload)
    {
        Interlocked.Increment(ref _eventsPublished);

        foreach (var channel in _clients.Values)
        {
            channel.Writer.TryWrite(payload);
        }
    }

    private void Remove(Guid id)
    {
        if (_clients.TryRemove(id, out var channel))
        {
            channel.Writer.TryComplete();
        }
    }

    public sealed class Subscription : IAsyncDisposable
    {
        private readonly EventHub _hub;
        private readonly Guid _id;

        internal Subscription(EventHub hub, Guid id, ChannelReader<string> reader)
        {
            _hub = hub;
            _id = id;
            Reader = reader;
        }

        public ChannelReader<string> Reader { get; }

        public ValueTask DisposeAsync()
        {
            _hub.Remove(_id);
            return ValueTask.CompletedTask;
        }
    }
}