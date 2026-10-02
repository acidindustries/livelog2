namespace Livelog.Server.Ingestion.Events;

public sealed record RequestEndEvent : LivelogEvent
{
    public int? StatusCode { get; init; }
    public TimeSpan? Duration { get; init; }
}