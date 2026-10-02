namespace Livelog.Server.Ingestion.Events;

public sealed record SpacerEvent : LivelogEvent
{
    public string? Label { get; init; }
}