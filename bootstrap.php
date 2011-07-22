<?php

require_once __DIR__.'/tests/openIdTestCase.php';

// 3rd party
require_once __DIR__.'/lib/lightopenid/openid.php';
require_once __DIR__.'/lib/lightopenid/provider/provider.php';

// okapi
require_once __DIR__.'/../../inc/api/command.php';
require_once __DIR__.'/inc/api/Client/request.php';
require_once __DIR__.'/inc/api/Client/response.php';
require_once __DIR__.'/inc/api/Provider/provider.php';

require_once __DIR__.'/inc/api/Exceptions/ErrorException.php';
require_once __DIR__.'/inc/api/Exceptions/InvalidArgumentException.php';
require_once __DIR__.'/inc/api/Exceptions/InvalidAxAttributeTypeException.php';
require_once __DIR__.'/inc/api/Exceptions/AuthenticationFailed.php';
require_once __DIR__.'/inc/api/Exceptions/AuthenticationRequest.php';
require_once __DIR__.'/inc/api/Exceptions/AssociationRequest.php';

require_once __DIR__.'/inc/api/command/openid/openid_provider.php';