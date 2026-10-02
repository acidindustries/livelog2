namespace Livelog.Server.Ingestion.Values;

public sealed record IntegerValue : Variable
{
    public required long Value { get; init; }
}