using System.Collections.Concurrent;
using Livelog.Server.Connections;
using Livelog.Server.Ingestion.Events;

namespace Livelog.Server.Session;

public sealed class EventStore
{
    private readonly EventHub _eventHub;

    private readonly ConcurrentDictionary<string, DebugSession> _sessions = new();
    private readonly int _maxSessions;
    private readonly int _maxEventsPerSession;
    private long _nextSequence;

    public EventStore(EventHub eventHub, int maxSessions = 100, int maxEventsPerSession = 10_000)
    {
        _eventHub = eventHub;
        _maxSessions = maxSessions;
        _maxEventsPerSession = maxEventsPerSession;
    }

    public void Add(LivelogEvent @event)
    {
        var sequence = Interlocked.Increment(ref _nextSequence);

        @event.Sequence = sequence;

        var session = _sessions.GetOrAdd(@event.SessionId, sessionId => new DebugSession
        {
            Id = sessionId,
            CreatedAt = @event.Timestamp,
        });

        lock (session)
        {
            session.Add(@event, _maxEventsPerSession);
        }

        TrimSessions();

        _eventHub.Publish(@event);
    }

    public IReadOnlyList<DebugSession> GetSessions() =>
        [.. _sessions.Values.OrderByDescending(x => x.LastEventAt)];

    public DebugSession? GetSession(string sessionId) =>
        _sessions.TryGetValue(sessionId, out var session) ? session : null;

    public void Clear()
    {
        _sessions.Clear();
    }

    private void TrimSessions()
    {
        while (_sessions.Count > _maxEventsPerSession)
        {
            var oldest = _sessions.Values
                .MinBy(s => s.LastEventAt);

            if (oldest is null)
            {
                return;
            }

            _sessions.TryRemove(oldest.Id, out _);
        }
    }
}