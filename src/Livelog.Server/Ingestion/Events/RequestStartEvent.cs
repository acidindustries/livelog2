namespace Livelog.Server.Ingestion.Events;

public sealed record RequestStartEvent : LivelogEvent
{
    public required string Method { get; init; }
    public required string Path { get; init; }
}