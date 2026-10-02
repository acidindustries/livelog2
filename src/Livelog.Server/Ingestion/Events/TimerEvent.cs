namespace Livelog.Server.Ingestion.Events;

public sealed record TimerEvent : LivelogEvent
{
    public required string Name { get; init; }
    public required TimeSpan Duration { get; init; }
}