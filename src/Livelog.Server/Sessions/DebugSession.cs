using Livelog.Server.Ingestion.Events;

namespace Livelog.Server.Session;

public sealed class DebugSession
{
    private readonly List<LivelogEvent> _events = [];
    public required string Id { get; init; }
    public DateTimeOffset CreatedAt { get; init; }
    public DateTimeOffset LastEventAt { get; private set; }
    public IReadOnlyList<LivelogEvent> Events => _events;

    internal void Add(LivelogEvent @event, int maxEvents)
    {
        _events.Add(@event);

        if (@event.Timestamp > LastEventAt)
        {
            LastEventAt = @event.Timestamp;
        }

        if (_events.Count <= maxEvents)
        {
            return;
        }

        _events.RemoveRange(0, _events.Count - maxEvents);
    }
}