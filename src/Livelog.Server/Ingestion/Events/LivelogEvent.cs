using System.Text.Json.Serialization;

namespace Livelog.Server.Ingestion.Events;

[JsonPolymorphic(TypeDiscriminatorPropertyName = "type")]
[JsonDerivedType(typeof(MessageEvent), "message")]
[JsonDerivedType(typeof(SpacerEvent), "spacer")]
[JsonDerivedType(typeof(DumpEvent), "dump")]
[JsonDerivedType(typeof(GroupStartEvent), "group-start")]
[JsonDerivedType(typeof(GroupEndEvent), "group-end")]
[JsonDerivedType(typeof(ExceptionEvent), "exception")]
[JsonDerivedType(typeof(RequestStartEvent), "request-start")]
[JsonDerivedType(typeof(RequestEndEvent), "request-end")]
[JsonDerivedType(typeof(TimerEvent), "timer")]
public abstract record LivelogEvent
{
    public required int Version { get; init; } = 1;
    public long Sequence { get; internal set; }
    public required Guid Id { get; init; }
    public required DateTimeOffset Timestamp { get; init; }
    public string? SessionId { get; init; }
    public string? RequestId { get; init; }
    public EventSource? Source { get; init; }
}