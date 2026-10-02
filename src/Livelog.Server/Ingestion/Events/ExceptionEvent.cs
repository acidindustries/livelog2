namespace Livelog.Server.Ingestion.Events;

public sealed record ExceptionEvent : LivelogEvent
{
    public required string ExceptionType { get; init; }
    public required string Message { get; init; }
    public string? StackTrace { get; init; }
}