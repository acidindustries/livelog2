namespace Livelog.Server.Ingestion.Events;

public sealed record EventSource
{
    public required string File { get; init; }
    public int? Line { get; init; }
}