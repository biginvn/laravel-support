<?php

namespace Bigin\Support\Contracts;

interface HttpCodeContract
{
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const UNPROCESSABLE_ENTITY = 422;
    const INTERNAL_SERVER_ERROR = 500;
}
