namespace Livelog.Server.Ingestion.Values;

public sealed record FloatValue : Variable
{
    public required double Value { get; init; }
}