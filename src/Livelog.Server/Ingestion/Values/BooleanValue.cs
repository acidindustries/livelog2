namespace Livelog.Server.Ingestion.Values;

public sealed record BooleanValue : Variable
{
    public required bool Value { get; init; }
}