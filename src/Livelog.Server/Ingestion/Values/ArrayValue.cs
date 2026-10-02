namespace Livelog.Server.Ingestion.Values;

public sealed record ArrayValue : Variable
{
    public required IReadOnlyList<ArrayItem> Items { get; init; }
}

public sealed record ArrayItem
{
    public required string Key { get; init; }
    public required Variable Value { get; init; }
}