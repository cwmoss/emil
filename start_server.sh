#!/bin/bash
# unfortunately this does't work with dredd

#error: Command to start backend server process failed, exiting Dredd Error: spawn start_testserver.sh ENOENT
#    at Process.ChildProcess._handle.onexit (internal/child_process.js:267:19)
#    at onErrorNT (internal/child_process.js:467:16)
#    at processTicksAndRejections (internal/process/task_queues.js:84:21)

php -S 127.0.0.1:1199 -t public/ public/index.php


