Cupons API
===========

Making a Request
----------------

### Response
All responses are returned in a json encoded format.

### Errors
All successfull responses have a HTTP status code of 200. However, when an error occures, this is not the case.

In case of an error, the response has the following format:
```json
{
    "error": {
        "code": 207, // error code
        "message": "coupon not found" // error messge
    }
}
```

Resources
---------
  - /user/check
  - /coupons
  - /coupons/latest
  - /coupons/[id]
  - /coupons/[id]/redeem
  - /coupons/[id]/rate
  - /categories
  - /merchants
  - /merchants/[id]
  - /merchants/[id]/coupons
  - /contracts
  - /leads
  - /getAPPConfig
  - /getUserPreferences
  - /setUserPreferences  
  - /setPunchCard


### POST /mailToMerchant
Query parameters:
- merchant_email
- customer_email
- customer_subject
- customer_message
Result
{
    "success": {
        "code": 200,
        "message": "Thank You"
    }
}
Result
{
    "error": {
        "code": 203,
        "message": "Missing or Invalid email"
    }
}


### POST /setPunchCard
Query parameters:
- account_id        - merchant id: 2576B841-BF2C-E111-B7CD-0026B9533489
- user_email        - any email
- punchcard_id      - 1
- punch_code        - 1234

{
    "message": "Punch Card was successfully punched.",
    "punch_limit": "5",
    "punch_usage": "1"
}
{
    "message": "Punch Card was successfully punched and you're gonna be rewarded.",
    "punch_limit": "5",
    "punch_usage": "5"
}


### GET /getAPPConfig - Get Update Information 
Query parameters:
  - No param
Result:
{
    "config": [
        {
            "app_update_new_android": "0",
            "app_update_force_android": "0",
            "app_update_new_ios": "0",
            "app_update_force_ios": "0",
            "app_update_url_android": "https://play.google.com/",
            "app_update_url_ios": "https://store.apple.com/",
            "app_update_info_android": "New Android update is available",
            "app_update_info_ios": "New iOS update is available"
        }
    ]
}

Info:
'app_update_new': 
- 0 no update
- 1 new update available
'app_update_force': 
- 0 can use without update
- 1 user must update before use app


### GET /getUserPreferences - Get Update Information 
Query parameters:
  - email
Result
{
    "error": {
        "code": 203,
        "message": "Missing or Invalid email"
    }
}
{
    "error": {
        "code": 207,
        "message": "User not found"
    }
}
{
    "usersPreferences": {
        "id": "1",
        "user_id": "42",
        "email": "office@activemall.ro",
        "distance": "5",
        "categories": "1,2,3",
        "ereceipt": "1",
        "notifications": "1",
        "notifications_silent": "0",
        "wallet_code": "1234", {or null if is not defined}
        "view_zip": "0"
    }
}

{
    "usersPreferences": {
        "id": "1",
        "user_id": "42",
        "email": "activemall.ro@gmail.com",
        "distance": "10",
        "categories": "1,2",
        "ereceipt": "1",
        "notifications": "1",
        "notifications_silent": "1",
        "wallet_code": null,
        "wallet_coupons": "1,2,3",
        "view_zip": "0"
    }
}

### GET /setUserPreferences - Get Update Information 
Query parameters: all are required and not empty
  - email
  - distance
  - categories
  - ereceipt
  - notifications
  - notifications_silent
  - wallet_code - optional
  - wallet_coupons - optional
  - view_zip - trebuie trimis mereu. daca e gol php interpreteaza ca 0. 0 false, 1 true

Result
{
    "error": {
        "code": 2,
        "message": "Missing parameter: email"
    }
}
{
    "error": {
        "code": 3,
        "message": "Empty parameter: email"
    }
}
{
    "error": {
        "code": 3,
        "message": "Wallet Code length is less than 4 characters."
    }
}
{
    "message": "success"
}


### GET /user/check - Check user credentials
Query/Form parameters:
  - login - User login
  - password - User password


### GET /coupons - Search for coupons
Query parameters:
  - ids - Ids of the coupons to get. When this parameter is provided, all others are ignored. Optional.
  - zip - Zip code at which to look for coupons. Optional.
  - coords - Coordinates at which to look for coupons. Format should be `[lat],[long]`. Optional.
  - radius - Radius in which to search for coupons. Default is 5. Should be between 1 and 25.
  - cat - Comma-separated list of category ids by which the search if filtered. Optional.
  - search - String to search in the coupons name. Optional.
  - merchant - Filter by merchant id. Optional.
  - phone - Filter by phone number. The phone field should only be composed of numbers. Optional.
  - types - Comma-separated list of coupon types to filter by. Optional. Valid options are: local, national, filter and splash. Default: local,national
  - status - Coupon status
  - since - Filter by creation date. The phone field should only be a unix timestamp. Optional.
  - start - Index of of the first result. Used for pagination. Optional.
  - limit - Number of results to return. Used for pagination. Default 20. Optional.

Results are ordered by distance from the given coordinates, ascending.


### POST /coupons - Create a coupon
Post parameters:
  - merchant - Merchant id. Required.
  - contract - The contract number. Required.
  - title - Coupon title. Required.
  - category - The category id. Required.
  - note - Coupon notes. Optional.
  - keywords - Comma separated keywords. Optional.
  - business - Business name. Required.
  - business_email - Business email. Required.
  - website - Business website. Optional.
  - store - Store name. Required
  - store_number - Store number. Required.
  - cycle - Cycle number. Required.
  - expire - Expire date. Required.
  - address - Address. Required.
  - type - Coupon type. Optional. Defaults to local. Valid options are: local, national, filter and splash.
  - city - City. Required.
  - state - State. Required.
  - zip - Zip. Required.

Files:
  - list_img - Image to use when listing coupon. Required.
  - details_img - Detailed coupon image. Required.

Both images should be in JPEG format with a width between 600 and 800 pixels.


### GET /coupons/latest - Newly added coupons
Query parameters:
  - since - Only return coupons newer than this date. Should be an unix timestamp. Optional.
  - cat - Comma-separated list of category ids by which the search if filtered. Optional.
  - start - Index of of the first result. Used for pagination. Optional.
  - limit - Number of results to return. Used for pagination. Default 20. Optional.

Results are ordered by distance from the given coordinates, ascending.


### GET /coupons/redeems - Show redeems for the last 24 hours
Query parameters:
  - user_id - Filter by user id. Optional.
  - coupon_id - Filter by coupon id. Optional.
  - merchant_id - Filter by merchant id. Optional.
  - phone_id - Filter by phone id. Optional.
  - email - Filter by email. Optional.
  - since - Only return records since specified date. Unix timestamp. Default last 24hours. Optional.
  - until - Only return records until specified date. Unix timestamp. Optional.


### GET /coupons/[id] - Get details for a coupon
Path parameters:
  - id - The id of the coupon.
  - email - optional

EX:
https://couponsapi.rtui.com/coupons/373191?email=office@activemall.ro
Response:
{
    "coupon_image_url": "http://www.rtui.com/uploads/coupons/",
    "coupon": {
        "id": "373191",
        "merchant_id": "2576B841-BF2C-E111-B7CD-0026B9533489",
        "name": "Goodyear Auto Service Center",
        "description": "",
        "filename": "373191-goodyear-auto-service-center.jpg",
        "title": "Conventional oil change $17.93",
        "title2": "Maxlife Synthetic Blend oil $29.93",
        "title3": "Synpower full synthetic oil $49.93",
        "title4": "",
        "title5": "",
        "alias": "view-coupon/373191-goodyear-auto-service-center-scotts",
        "status": "1",
        "number": "0405",
        "website": "www.goodyearautoservice.com",
        "phone": "(260) 484-7761",
        "rating": null,
        "catid": "17",
        "contract": "J132197",
        "business_email": "melody.nobis@goodyear.com",
        "category_title": "Maintenance/Oil Change",
        "category_id": "17",
        "store": "Scotts",
        "address": "4106 Coldwater Rd",
        "city": "Fort Wayne",
        "zip": "46805",
        "state": "IN",
        "lat": "41.114835",
        "lng": "-85.134442",
        "cycle": "B4",
        "keywords": "",
        "type": "0",
        "created_date": "1416808800",
        "modified_date": "0",
        "expire_date": "1426395600",
        "pid": "388"
    },
    "merchant_image": "http://www.rtui.com/images/places/in-fort-wayne/goodyear-auto-service-center_1.jpg",
    "merchant_default_image_url": "http://www.rtui.com/images/places/",
    "place": {
        "id": "388",
        "name": "Goodyear Auto Service Center",
        "types": "store",
        "url": "",
        "website": "http://www.goodyearautoservice.com/home.jsp?dynid=GooglePlaces-_-3181-_-6769-_-IN&ecid=Google_Places_6769",
        "vicinity": "4106 Coldwater Rd, Fort Wayne",
        "address": "4106 Coldwater Rd",
        "city": "Fort Wayne",
        "state": "IN",
        "zip": "46805",
        "formatted_address": "4106 Coldwater Rd, Fort Wayne, IN 46805, United States",
        "phone_number": "(260) 484-7761",
        "international_phone_number": "",
        "lat": "41.114835",
        "lng": "-85.134442",
        "opening_hours": [
            "Monday: 7:00 am – 7:00 pm",
            "Tuesday: 7:00 am – 7:00 pm",
            "Wednesday: 7:00 am – 7:00 pm",
            "Thursday: 7:00 am – 7:00 pm",
            "Friday: 7:00 am – 7:00 pm",
            "Saturday: 7:30 am – 6:00 pm",
            "Sunday: 9:00 am – 4:00 pm"
        ],
        "images": "[\"in-fort-wayne\\/goodyear-auto-service-center_1.jpg\"]",
        "events": "",
        "rating": "",
        "rating_reference": "",
        "reviews": "[{\"aspects\":[{\"rating\":3,\"type\":\"overall\"}],\"author_name\":\"Nichole P.\",\"author_url\":\"https:\\/\\/plus.google.com\\/108873697533114996003\",\"language\":\"en\",\"rating\":5,\"text\":\"This is my go-to place for anything to do with my car. I've bought tires here on a couple of occasions and it's always a good experience. I also come in regularly for oil changes and tire balancing. Everyone is always friendly and they are quick to get the job done. It's great because I can take my kids with me and not have to worry about them getting bored, because they have an awesome place set up for kids to color and play. The big screen tv, vending machines, and free coffee are a big bonus too when you're waiting! I tell all of my friends how nice it is here. \",\"time\":1389206961}]"
    },
    "merchant_campaigns_card_url": "http://www.rtui.com/images/campaigns/",
    "campaigns": [
        {
            "id": "1",
            "account_id": "2576B841-BF2C-E111-B7CD-0026B9533489",
            "campaign_name": "Campaign #5",
            "start_date": "2014-01-01",
            "end_date": "2015-01-31",
            "offer": "6 this for 5 that",
            "campaign_coupons": "362067,362248,362709,362741,361517,362128,361267,362703,361197",
            "campaign_stores": "1",
            "image": "5.jpg",
            "value": "15"
        }
    ],
    "merchant_punchcard_url": "http://www.rtui.com/images/punchcards/",
    "punchcards": [
        {
            "id": "1",
            "account_id": "2576B841-BF2C-E111-B7CD-0026B9533489",
            "punch_name": "Free car wash",
            "punch_offer": "Free car wash on 5 usage",
            "punch_image": "1.jpg",
            "punch_limit": "5",
            "punch_usage": "1"
        }
    ],
    "googleStaticMap": "https://couponsapi.rtui.com/staticMaps/373191.jpg"
}





### POST /coupons/[id] - Update details for a coupon
Path parameters:
  - id - The id of the coupon.

Post parameters:
  - merchant - Merchant id.
  - contract - The contract number.
  - title - Coupon title.
  - category - The category id.
  - note - Coupon notes.
  - keywords - Comma separated keywords.
  - business - Business name.
  - business_email - Business email.
  - website - Business website.
  - store - Store name. Required
  - store_number - Store number.
  - cycle - Cycle number.
  - expire - Expire date.
  - address - Address.
  - city - City.
  - state - State.
  - zip - Zip.

Files:
  - list_img - Image to use when listing coupon. Required.
  - details_img - Detailed coupon image. Required.


### DELETE /coupons/[id] - Delete a coupon
Path parameters:
  - id - The id of the coupon.


### POST /coupons/[id]/redeem - Redeem a coupon
Path parameters:
  - id - The id of the coupon.

Query/Form parameters:
  - email - The email of the person which is redeeming the coupon. Required.
  - username - Username. Optional.
  - coordinates - The coordinates at which the coupon is being redeemed. Format should be `[lat]:[long]`. Required.
  - phone_id - The UUID of the phone. Required.
  - receipt - This field should be set if the person wants a receipt.

This request only returns a success message.


### POST /coupons/[id]/rate - Rate a coupon
Path parameters:
  - id - The id of the coupon.

Query/Form parameters:
  - rating - The rating which is given to the coupon. Should be between 1 and 5. Required.

This request only returns a success message.


### GET /categories - List of all coupon categories
All categories are found as a list under the `categories` key of the result.


### GET /merchants - Browse all merchants
Query/Form parameters:
  - ids - Ids of the merchants to get. When this parameter is provided, all others are ignored. Optional.
  - search - Search by merchant name. Optional.
  - email - Filter by merchant email. Optional.
  - phone - Filter by merchant phone. Optional.
  - start - Index of of the first result. Used for pagination. Optional.
  - limit - Number of results to return. Used for pagination. Default 20. Optional.


### GET /merchants/[id] - Get details for a specific merchant
Path parameters:
   - id - The id of the merchant


### GET /merchants/[id]/coupons - Get coupons provided by merchant
Path parameters:
  - id - The id of the merchant

Query/Form parameters:
  - zip - Zip code at which to look for coupons. Optional.
  - coords - Coordinates at which to look for coupons. Format should be `[lat],[long]`. Optional.
  - radius - Radius in which to search for coupons. Default is 5. Should be between 1 and 25.
  - phone - Filter by phone number. The phone field should only be composed of numbers. Optional.
  - types - Comma-separated list of coupon types to filter by. Optional. Valid options are: local, national, filter and splash.
  - start - Index of of the first result. Used for pagination. Optional.
  - limit - Number of results to return. Used for pagination. Default 20. Optional.


### GET /contracts/[id] - Get details for a specific contract
Path parameters:
  - id - The id of the merchant


### POST /leads - Create a call-in lead
Query/Form parameters:
  - name - Contact name. Required.
  - business -Business name. Optional.
  - email - Contact email. Required
  - address - Contact address. Required.
  - city - Contact city. Required.
  - state - Contact state. Required.
  - zip - Contact zip. Required.
  - phone - Contact phone. Required.
  - country - Contact country. Accepted values are US and Canada. Required.
  - message - Message. Required.
  - coords - Coordinates. Required.
# public12webas
