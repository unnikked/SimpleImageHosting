#Simple Image Hosting

I decided to create this simple image hosting script in order to enable sharing of pictures easily in an distributed team. 

You don't need any login credential, just upload a file and share your link. 

I designed it to be used on a restricted environment so I've not taken care about randomness of the image retrieving url. 

An image is described by the resource `/:id` so anyone can attempt to view other files by simply changing that number, this is why it is recommended to use it on a private network.

The file `clean.php` helps to clean old content easily, I recommend to set a cronjob: 

```
0 0 * * 0 php path/to/clean.php
``` 

## Installation

- Download this script in your web server
- Fill database information into `config.ini` file
- Go to `/install`
- Set into `config.ini` the value of `installed` to `true` (`installed=true`)
- Enjoy your script

##Configuration
Into `config.ini` file you can set some custom parameters

- `days`: expiry time for your files, defaults to `7`
- `max_file_size`: expressed in MB is the max file size you can upload NOTE: not the same as in PHP ini value!

##API

You can interact with this script in a REST like manner, no auth token or login is required. 

### Upload file

To upload a file simply make a POST request to the base url of the app `POST /`

Example using curl:

```bash
curl -X POST \
-H "X-Requested-With: XMLHttpRequest" \
-F filedata=@path/to/your/file \
http://your.domain.here/
```

Example response:

- success
```json
{  
    "status":"success",
    "timestamp":1418064159,
    "message":{  
        "id":"4"
    }
}
```

- failed

```json
{  
    "status":"failed",
    "timestamp":1418064410,
    "message":{  
        "error":"File unsupported"
    }
}
```