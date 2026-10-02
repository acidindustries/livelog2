namespace Livelog.Server.Ingestion.Events;

public sealed record GroupStartEvent : LivelogEvent
{
    public required string Name { get; init; }
}