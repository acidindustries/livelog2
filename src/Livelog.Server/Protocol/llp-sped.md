# LiveLog Protocol Specification

## Protocol payload 

```json
{
    "version": 1,
    "id": "...",
    "timestamp":"...",
    "type": "...",
    "sessionId":"...",
    "requestId":"...",
    "source": {},
    "data": {},
}
```

| Field | Purpose |
| --- | --- |
| version | Protocol version |
| id | Event ID |
| timestamp | Client timestamp |
| type | Event type |
| sessionId | Debugging session |
| requestId | HTTP/request identifier |
| source | File/line information |
| data | Event-specific payload |

## Event Types

### message
```json
{
    "type": "message",
    "data": {
        "message": "Hello, world"
    }
}
```

### dump
```json
{
    "type":"dump",
    "data": {
        "label": "User",
        "value": {}
    }
}
```

### spacer
```json
{
    "type": "spacer",
    "data": {
        "label": "SPACERTEXT"
    }
}
```

### group-start
```json
{
    "type": "group-start",
    "data": {
        "name": "GROUPNAME"
    }
}
```

### group-end
```json
{
    "type": "group-end",
    "data": {
        "name": "GROUPNAME"
    }
}
```

### exception
```json
{
    "type": "exception",
    "data": {
        ...
    }
}
```

### request-start
```json
{
    "type": "request-start",
    "data": {
        ...
    }
}
```

### request-end
```json
{
    "type": "request-end",
    "data": {
        ...
    }
}
```

### timer
```json
{
    "type": "timer",
    "data": {
        ...
    }
}
```