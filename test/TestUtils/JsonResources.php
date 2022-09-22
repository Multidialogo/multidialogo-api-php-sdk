<?php


namespace multidialogo\client\test\TestUtils;


class JsonResources
{
    const AuthOkResponse = <<<json
{
    "status": "CREATED",
    "data": {
        "id": "25ad1beadfeb089cb5bd0ea20da8a610.0004178967cf186762382528d7e994ba081z8111551061",
        "type": "auth-tokens",
        "attributes": {
            "token": "25ad1beadfeb089cb5bd0ea20da8a610.0004178967cf186762382528d7e994ba081z8111551061",
            "category": "Bearer",
            "createdAt": "2020-10-01T11:18:38Z",
            "expireAt": "2020-10-01T14:18:38Z",
            "refreshToken": "3a7659350cea59ef215cba24118acfb3.f152d87a5a3cbda3cdb688bc7beb155f00612z8111551061",
            "refreshTokenExpireAt": "2020-10-16T11:18:38Z"
        }
    }
}
json;
}