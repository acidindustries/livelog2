namespace Livelog.Server.Ingestion.Values;

public sealed record ObjectValue : Variable
{
    public required string Class { get; init; }
    public required IReadOnlyList<ObjectProperty> Properties { get; init; }
}

public sealed record ObjectProperty
{
    public required string Name { get; init; }
    public required Variable Value { get; init; }
}