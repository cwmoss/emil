# emil

microservice for transactional email

self hosted email service

## goals

most of my apps require sending emails. while migrating to the cloud stack, i felt the need to have an email service via http that works the same way as my old in-app functions that i can host myself. most of time i don't need most of the functionality of the big boys' services. so i don't want to subscribe to any service just to be able to send a bunch of mails a month. what i needed was templating. having a single layout for all html emails of one project. 

you will need a SMTP account for sending.

you will have to create templates.

you can't send a raw email here. everything is based on prepared templates.

## example request

    # endpoint of service: http://acme.com/emil
    # org: acme
    # project: niceproject
    # template: welcome
    # POST email/{org}/{project}/send/{template}
    curl http://localhost:1199/send/acme/niceproject/welcome \
      -H "X-Emil-Api: 9ecc433c..."
      -d '{"name":"strange guy","to":"latoya@myspace.com","confirm_token":"mM-Juhu99-EEnlf"}'

    # without project and with basic auth -u api:organization-api-key
	 curl -v http://localhost:1199/send/rw/welcome \
	   -u api:9ecc433c... \
	   -d '{"name":"strange guy","to":"rw@20sec.net","from":"rw@20sec.net"}' -H "X-Emil-Api: 9ecc433c..."

## quick start

	 git clone https://github.com/cwmoss/emil.git
	 cd emil
	 composer install
	 # follow the instructions



## Templates

Template Engine is [LightnCandy](https://github.com/zordius/lightncandy) -- handlebars for php

### Template Naming Conventions

* Layout templates starts with `__` (two underscores)
* Partials starts with `_` (one underscore)
* Message Templates starts with lowercase character

## Authorization

all admin actions `/admin` must be authorized by either a http header `X-Emil-Admin` containing the admin secret or the http basic auth header with username `admin` and the admin secret as password.

all email sending `/send` or management `/manage` api actions must be authorized by either a http header `X-Emil-Api` containing the organizations secret or the http basic auth header with username `api` and the organizations secret as password.

## API


### Send Message


### Manage Templates

Upload (multiple) Templates

`POST /manage/ORG/PROJECT`

	curl http://localhost:1199/manage/ORG/PROJECT/upload -F "u[]=@welcome.html" -F "u[]=@welcome.txt"  -F "u[]=@logo.png" -F "u[]=@__default.html"

Upload single Template

`PUT /manage/ORG/PROJECT/TEMPLATENAME.HTML`

	curl http://localhost:1199/manage/ORG/PROJECT/upload/logo.png -T logo.png


### Admin

Create Organization

## 5 different ways of configuring your server

### 1/ php server mode (ONLY FOR DEVELOPMENT)

all the examples are refering to this setup, since this is the easiest way for development

	 `php -S localhost:1199 -t public/`

	 # your endpoint
	 http://localhost:1199
	 # example: list organizations
	 http://localhost:1199/admin/orgs

### 2/ no rewrites, everything exposed to webserver (ONLY FOR DEVELOPMENT)

	 # your endpoint
	 http://localhost/dev/projects/emil/public/index.php
	 # example: list organizations
	 http://localhost/dev/projects/emil/public/index.php/admin/orgs

### 3/ rewrites are active, everything exposed to webserver (ONLY FOR DEVELOPMENT)

	 # copy dot.htaccess to public/.htaccess
	 cp dot.htacces public/.htaccess

	 # your endpoint
	 http://localhost/dev/projects/emil/public
	 # example: list organizations
	 http://localhost/dev/projects/emil/public/admin/orgs

### 4/ rewrites are active, /public is exposed by webserver via link

	 # copy dot.htaccess to public/.htaccess
	 cp dot.htacces public/.htaccess

	 # link /public to webserver-root/emil
	 ln -S /Users/rw/dev/emil/public /usr/local/var/www/emil

	 # your endpoint
	 http://localhost/emil
	 # example: list organizations
	 http://localhost/emil/admin/orgs

### 5/ rewrites are active, /public is exposed by webserver via link, environment set in webserver config (RECOMMENDED FOR PRODUCTION)

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

## Credits

* swiftmailer/swiftmailer, sending emails
* zordius/lightncandy, handlebars implementation for php
* bramus/router, routing lib
* vlucas/phpdotenv, dotenv for php
* mnapoli/front-yaml, frontmatter parsing
* starter templates, salted by ..., simpleresponsive by leemunroe/responsive-html-email-template 
* acme logo by [Mackenzie Child](http://acmelogos.com/)