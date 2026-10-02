namespace Livelog.Server.Ingestion.Values;

public sealed record StringValue : Variable
{
    public required string Value { get; init; }
}