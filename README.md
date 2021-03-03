# emil

microservice for transactional email

self hosted email service

### example request

    # endpoint of service: http://acme.com/emil
    # org: acme
    # type: contact
    # POST submission/{org}/send/{type}
    curl --header "Content-Type: application/json" \
      http://acme.com/emil/acme/send/welcome \
      -d '{"name":"strange guy","email":"latoya@myspace.com","message":"i like your website, please call back"}'

