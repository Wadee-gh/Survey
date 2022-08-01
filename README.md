# Project Setup 

### Database

create local database, and restore it from sql/dump.sql you can find this file on the dev branch. Ask for latest version if you run into any problem using it.

### configuration

copy sample configuration

```sh
cd application/config/
cp config-sample-mysql.php config.php
```

configure with your local sql accesss

change urlFormat to path at config.php:42

```
        'urlManager' => array(
            'urlFormat' => 'path',
            'rules' => array(
                // You can add your own rules here
            ),
            'showScriptName' => true,
        ),
```


