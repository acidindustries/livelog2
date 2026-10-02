using System.Net.WebSockets;
using System.Security.Cryptography.X509Certificates;
using System.Text;
using System.Text.Json;
using Livelog.Server.Connections;
using Livelog.Server.Ingestion;
using Livelog.Server.Ingestion.Events;
using Livelog.Server.Session;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddOpenApi();
builder.Services.AddSingleton<EventHub>();
builder.Services.AddSingleton<EventStore>();
builder.Services.AddSingleton<WebsocketConnection>();
builder.Services.ConfigureHttpJsonOptions(options =>
    options.SerializerOptions.AllowOutOfOrderMetadataProperties = true);

var app = builder.Build();

if (app.Environment.IsDevelopment())
{
    app.MapOpenApi();
}

app.UseWebSockets();
app.UseDefaultFiles();
app.UseStaticFiles();

app.MapGet("/api/status", (EventHub hub) => Results.Ok(new
{
    connectedBrowsers = hub.ConnectionCount,
    eventsReceived = hub.EventsReceived
}));

app.MapGet("/api/sessions", (EventStore store) => Results.Ok(store.GetSessions()));

app.MapGet("/api/sessions/{sessionId}", (string sessionId, EventStore store) =>
{
    var session = store.GetSession(sessionId);

    return session is null
        ? Results.NotFound()
        : Results.Ok(session);
});

app.MapDelete("/api/sessions", (EventStore store) =>
{
    store.Clear();

    return Results.NoContent();
});

app.MapPost("/api/events", (LivelogEvent @event, EventStore store) =>
{
    if (@event.Version != 1)
    {
        return Results.BadRequest(new { error = "Only LLP version 1 is supported." });
    }

    store.Add(@event);

    return Results.Accepted(value: new { @event.Id });
})
.WithName("IngestEvent");

app.Map("/api/live", async (HttpContext context, WebsocketConnection connection) =>
{
    if (!context.WebSockets.IsWebSocketRequest)
    {
        context.Response.StatusCode = StatusCodes.Status400BadRequest;
        return;
    }

    using var socket = await context.WebSockets.AcceptWebSocketAsync();
    await connection.HandleAsync(socket, context.RequestAborted);
});

app.Run();
