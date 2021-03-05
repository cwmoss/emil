# emil

microservice for transactional email

self hosted email service

### example request

    # endpoint of service: http://acme.com/emil
    # org: acme
    # project: niceproject
    # template: welcome
    # POST email/{org}/{project}/send/{template}
    curl --header "Content-Type: application/json" \
      http://acme.com/emil/email/acme/niceproject/send/welcome \
      -d '{"name":"strange guy","to":"latoya@myspace.com","confirm_token":"mM-Juhu99-EEnlf"}'


curl -F "image[]=@file1.gif" -F "image[]=@file2.gif"  http://localhost:8888/web/Upload.php

-F "u[]=@__twenty.html"


find ./ -name '*' -type f -exec curl -u USERNAME:PASSWORD -T {} http://www.example.com/folder/{} \;



9ecc433ceb0442256472acb00c1f262d76b702ecb2a93103e3ed0f67b13ae073

curl -v http://localhost/dev/emil/public/index.php/send/rw/welcome -d '{"name":"strange guy","to":"rw@20sec.net","from":"rw@20sec.net"}' -H "X-Emil-Api: 9ecc433ceb0442256472acb00c1f262d76b702ecb2a93103e3ed0f67b13ae073"

acme logo by [Mackenzie Child](http://acmelogos.com/)