using Livelog.Server.Ingestion.Values;

namespace Livelog.Server.Ingestion.Events;

public sealed record DumpEvent : LivelogEvent
{
    public string? Label { get; init; }
    public required Variable Value { get; init; }
}