#!/bin/bash
# unfortunately this does't work with dredd

#error: Command to start backend server process failed, exiting Dredd Error: spawn start_testserver.sh ENOENT
#    at Process.ChildProcess._handle.onexit (internal/child_process.js:267:19)
#    at onErrorNT (internal/child_process.js:467:16)
#    at processTicksAndRejections (internal/process/task_queues.js:84:21)

EMIL_ADMIN_KEY=testMe EMIL_JWT_SECRET=TLSbkGXD5TtkrmxLhV1Roy3Nqwvl9LfygklDa2ZDJ2uDTlDHv2JI59Eq4aa5YNxSq0iSg3-D-PEcAF4YC-UwfA php -S localhost:1199 -t public/ public/index.php


