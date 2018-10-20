# Base62 service

This simple service for encoding and decoding any strings using _Base62_ algorithm. Homework for Otus PHP course.

## Usage
Base config placed in directory _config_.

### Starting server
You can start server in foreground or background (daemon) mode:

```
$ bin/server start -f           # Run in foreground mode
$ bin/server start              # Run in background mode
```

For stopping daemonized server use command:
```
$ bin/server stop
```

Also you can reload configs without manual stopping server:
```
$ bin/server reload
```

### Using client
Client has two simple commands: encode and decode.
```
$ bin/client encode "Hello World"
Result: 73XpUgyMwkGr29M

$ bin/client decode 73XpUgyMwkGr29M
Result: Hello World
```

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
