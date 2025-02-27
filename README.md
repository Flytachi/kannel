# Kannel (Smpp protocol)

![Icon](https://raw.githubusercontent.com/Flytachi/kannel/public/favicon.svg)

### 📌 Description

This project is designed to work with the SMPP protocol and serves as a bridge for integrating USSD responses. It processes incoming and outgoing messages, enabling seamless interaction with mobile operators and other SMPP services.
🔧 Features:

✅ Redirects subscriber requests to a specified API with the ability to receive dynamic responses.<br>
✅ Sends API responses back to the user via USSD.<br>
✅ Flexible SMPP parameter configuration for various use cases.<br>
✅ Web interface for optimal monitoring and management.<br>
✅ Console interface for command-line operations and automation.

This project simplifies USSD service integration with external APIs and enhances communication between users and services.

## Installation

### Requirements
- Redis host
- php 8.3
- composer
#### or
- Redis host
- Docker / docker-compose

### Settings (composer)
-- Be sure to set up an environment variable (Environment)

```sh
composer check-platform-reqs
```
#### Install all missing components
```sh
composer install
```

### Settings (docker)
-- Be sure to set up an environment variable (Environment)

#### Collect image
```sh
docker build -t smpp_kannel_server .
```
#### Launch container
```sh
docker run -d --name smpp_kannel_server --network bridge -p 8000:80 -v $(pwd):/var/www/html smpp_kannel_server
```

### Settings (docker-compose)
#### Launch container
```sh
docker compose up -d
```

<hr>

## Environment
The environment must contain a list of values. 
In an environment variable or in a `.env` file (example `app/.env`)
```.env
# Main settings
TIME_ZONE=Asia/Tashkent
DEBUG=false

# Logger settings
LOGGER_LEVEL_ALLOW=INFO,NOTICE,WARNING,ERROR,CRITICAL,ALERT,EMERGENCY
LOGGER_MAX_FILES=30
LOGGER_FILE_DATE_FORMAT="Y-m-d"
LOGGER_LINE_DATE_FORMAT="Y-m-d H:i:s P"

# Configuration Redis
REDIS_DBNAME=1
REDIS_HOST=127.0.0.1
REDIS_PASS=
REDIS_PORT=6379

# Configuration SMPP (Short Message Peer-to-Peer)
SMPP_HOST=<your_smpp_host>
SMPP_PORT=<your_smpp_port>
SMPP_LOGIN=<your_smpp_login>
SMPP_PASS=<your_smpp_password>

# Params SMPP
SMPP_PARAMS_LISTENER_RPS=100                            # read qty per/seccond for listener
SMPP_PARAMS_SENDER_RPS=100                              # read qty per/seccond for sender
SMPP_PARAMS_SENDER_QLN=<your_redis_listname>            # set '' default value -> 'smpp-kannel'
SMPP_PARAMS_SENDER_FROM="<your_smpp_from_address>"      # example '*100#'

SMPP_PARAMS_DLR_ON=<true/false>                         # params switch -> receive request on url
SMPP_PARAMS_DLR_URL=<your_receive_url>                  # receive url
SMPP_PARAMS_DLR_METHOD=<your_receive_method>            # receive method (GET, POST)
SMPP_PARAMS_DLR_META=""                                 # receive set static custom metta-data
SMPP_PARAMS_DLR_RESPONSIVE=<true/false>                 # receive wait for a response(json) and forward the response

WEB_ADMIN_USER=<admin_user>                             # Web interface (username)
WEB_ADMIN_PASS=<admin_pass>                             # Web interface (password)
```

<hr>

## Service command
Commands for service management! The shell must be responsive (php >= 8.3), 
otherwise the commands will not work
### Start service:
```sh
php extra run script app.service start 
```

### Stop service:
```sh 
php extra run script app.service stop 
```

### Service status:
```sh 
php extra run script app.service status 
```
<hr>

## Web

### Interface
<strong>started (php/composer)</strong>
started web interface
```sh 
php extra run serve --port=8000
```

In the browser, contact the address `http://0.0.0.0:8000/`<br>
For authorization, set the required values in the environment
`WEB_ADMIN_USER` and `WEB_ADMIN_PASS`

### DLR (requests)
GET request:
`http://0.0.0.0:8000/dlr?phoneNumber=<phone>&message=<message>&enableInput=<true/false>`<br>
POST request: -> `http://0.0.0.0:8000/dlr` (json)
```json
{
    "phoneNumber": "<phone>",
    "message": "<message>",
    "enableInput": "<true/false>"
}
```

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
