# emil

microservice for transactional email

self hosted email service

## example request

    # endpoint of service: http://acme.com/emil
    # org: acme
    # project: niceproject
    # template: welcome
    # POST email/{org}/{project}/send/{template}
    curl http://acme.com/emil/send/acme/niceproject/welcome \
      -H "X-Emil-Api: 9ecc433c..."
      -d '{"name":"strange guy","to":"latoya@myspace.com","confirm_token":"mM-Juhu99-EEnlf"}'

    # without project and with basic auth -u api:organization-api-key
	 curl -v http://acme.com/emil/send/rw/welcome \
	   -u api:9ecc433c... \
	   -d '{"name":"strange guy","to":"rw@20sec.net","from":"rw@20sec.net"}' -H "X-Emil-Api: 9ecc433c..."

## Quick Start

	 git clone https://github.com/cwmoss/emil.git
	 cd emil
	 composer install
	 # follow the instructions

## 4 different ways of configuring your server

### 1/ no rewrites, everything exposed to webserver (ONLY FOR DEVELOPMENT)

	 # your endpoint
	 http://localhost/dev/projects/emil/public/index.php
	 # example: list organizations
	 http://localhost/dev/projects/emil/public/index.php/admin/orgs

### 2/ rewrites are active, everything exposed to webserver (ONLY FOR DEVELOPMENT)

	 # copy dot.htaccess to public/.htaccess
	 cp dot.htacces public/.htaccess

	 # your endpoint
	 http://localhost/dev/projects/emil/public
	 # example: list organizations
	 http://localhost/dev/projects/emil/public/admin/orgs

### 3/ rewrites are active, /public is exposed by webserver via link

	 # copy dot.htaccess to public/.htaccess
	 cp dot.htacces public/.htaccess

	 # link /public to webserver-root/emil
	 ln -S /Users/rw/dev/emil/public /usr/local/var/www/emil

	 # your endpoint
	 http://localhost/emil
	 # example: list organizations
	 http://localhost/emil/admin/orgs

### 4/ rewrites are active, /public is exposed by webserver via link, environment set in webserver config (RECOMMENDED FOR PRODUCTION)

	 # add rewrite rules in apache location section <Location /emil>...</Location>
	 # add env in location section
	 # SetEnv EMIL_MAIL_TRANSPORT smtp://...
	 # SetEnv EMIL_ADMIN_KEY 449592d38c...

	 # link /public to webserver-root/emil
	 ln -S /Users/rw/dev/emil/public /usr/local/var/www/emil

	 # your endpoint
	 http://localhost/emil
	 # example: list organizations
	 http://localhost/emil/admin/orgs

there are certainly more combinations that are possible. just remember: dont use `.env` files in production. use real environment variables via nginx/apache virtuals host, docker, apache .htaccess etc.

## Templates

Template Engine is handlebars for php

### Template Naming Conventions

* Layout templates starts with `__` (two underscors)
* Partials starts with `_` (one underscore)
* Message Templates starts with lowercase character


## API


### Send Message


### Manage Templates

Upload (multiple) Templates

`POST /manage/ORG/PROJECT`

	curl http://localhost:8888/manage/ORG/PROJECT/upload -F "u[]=@welcome.html" -F "u[]=@welcome.txt"  -F "u[]=@logo.png" -F "u[]=@__default.html"

Upload single Template

`PUT /manage/ORG/PROJECT/TEMPLATENAME.HTML`

	curl http://localhost:8888/manage/ORG/PROJECT/upload/logo.png -T logo.png


### Admin

Create Organization



## Credits

* swiftmailer/swiftmailer, sending emails
* zordius/lightncandy, handlebars implementation for php
* bramus/router, routing lib
* vlucas/phpdotenv, dotenv for php
* mnapoli/front-yaml, frontmatter parsing
* starter templates, salted by ..., simpleresponsive by leemunroe/responsive-html-email-template 
* acme logo by [Mackenzie Child](http://acmelogos.com/)