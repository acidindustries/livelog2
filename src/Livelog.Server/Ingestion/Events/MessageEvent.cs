namespace Livelog.Server.Ingestion.Events;

public sealed record MessageEvent : LivelogEvent
{
    public required string Message { get; init; }
}