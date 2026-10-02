using System.Net.Http.Headers;
using System.Text.Json;
using Livelog.Server.Ingestion.Events;
using Livelog.Server.Ingestion.Values;
using Microsoft.VisualStudio.TestPlatform.CommunicationUtilities;
using Microsoft.VisualStudio.TestPlatform.CommunicationUtilities.Interfaces;

namespace Livelog.Server.Tests;

public class IngestionSerializationTests
{
    private readonly JsonSerializerOptions _options = new()
    {
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase
    };

    [Fact]
    public void Message_event_round_trip()
    {
        var original = new MessageEvent
        {
            Version = 1,
            Id = Guid.NewGuid(),
            Timestamp = DateTimeOffset.UtcNow,
            SessionId = "session-1",
            RequestId = "request-1",
            Message = "Hello, test!"
        };

        var json = JsonSerializer.Serialize<LivelogEvent>(original, _options);

        var result = JsonSerializer.Deserialize<LivelogEvent>(json, _options);

        var message = Assert.IsType<MessageEvent>(result);

        Assert.Equal(original.Id, message.Id);
        Assert.Equal(original.RequestId, message.RequestId);
        Assert.Equal(original.SessionId, message.SessionId);
        Assert.Equal(original.Message, message.Message);
    }

    [Fact]
    public void Object_dump_round_trips()
    {
        var original = new DumpEvent
        {
            Version = 1,
            Id = Guid.NewGuid(),
            Timestamp = DateTimeOffset.UtcNow,
            Label = "user",
            Value = new ObjectValue
            {
                Class = "App\\Models\\User",
                Properties = [
                    new ObjectProperty {
                        Name = "id",
                        Value = new IntegerValue {
                            Value = 42,
                        }
                    },
                    new ObjectProperty {
                        Name = "email",
                        Value = new StringValue {
                            Value = "user@test.com",
                        }
                    }
                ],
            }
        };

        var json = JsonSerializer.Serialize<LivelogEvent>(original, _options);

        var result = JsonSerializer.Deserialize<LivelogEvent>(json, _options);

        var dump = Assert.IsType<DumpEvent>(result);
        var obj = Assert.IsType<ObjectValue>(dump.Value);

        Assert.Equal("App\\Models\\User", obj.Class);
        Assert.Equal(2, obj.Properties.Count);

        var id = Assert.IsType<IntegerValue>(obj.Properties[0].Value);

        Assert.Equal(42, id.Value);
    }
}
