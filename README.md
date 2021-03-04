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