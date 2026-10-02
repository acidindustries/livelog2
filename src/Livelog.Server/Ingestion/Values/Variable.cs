using System.Text.Json.Serialization;

namespace Livelog.Server.Ingestion.Values;

[JsonPolymorphic(TypeDiscriminatorPropertyName = "kind")]
[JsonDerivedType(typeof(NullValue), "null")]
[JsonDerivedType(typeof(IntegerValue), "integer")]
[JsonDerivedType(typeof(BooleanValue), "boolean")]
[JsonDerivedType(typeof(FloatValue), "float")]
[JsonDerivedType(typeof(StringValue), "string")]
[JsonDerivedType(typeof(ArrayValue), "array")]
[JsonDerivedType(typeof(ObjectValue), "object")]
public abstract record Variable;