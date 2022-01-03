PROJECT NAME
===============

Description
-----------



Problems
--------


Commands
--------


Install Locally
---------------
0. Copy .env file to .env.local
    a. Replace DB credentials
    b. Replace BASE_PATH, PUBLIC_PATH paths and LINK_API link

1. $ composer install 
2. $ yarn install or npm install
3. $ php bin/console doctrine:database:create
4. $ php bin/console doctrine:migrations:migrate
5. $ yarn encore dev or npm run dev
6. $ symfony server:start ó utilizar docker leer -> ../docker/README.md

JWT Token
---------
    Generation
    a. Enable openssl in your Web Server
    b. Create folder api/config/jwt and go to config/jwt 

    c. Execute (If you are in windows, you need to download openssl binaries)
        
        openssl genrsa -out private.pem -aes256 4096

        Password: Look for it in .env -> JWT_PASSPHRASE

    d. openssl rsa -pubout -in private.pem -out public.pem

        Password: Look for it in .env -> JWT_PASSPHRASE

    e. Verify two new files in api/config/jwt.

Before Upload to Server
-----------------------

1. yarn encore prod
2. Replace /build/ by build/ in /public/build/manifest.json
