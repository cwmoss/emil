# emil

microservice for transactional email

self hosted email service

### example request

    # endpoint of service: http://acme.com/emil
    # org: acme
    # type: contact
    # POST submission/{org}/send/{type}
    curl --header "Content-Type: application/json" \
      http://acme.com/emil/email/acme/niceproject/send/welcome \
      -d '{"name":"strange guy","to":"latoya@myspace.com","confirm_token":"mM-Juhu99-EEnlf"}'

