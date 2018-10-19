<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2018 Serhii Popov
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Popov
 * @package Popov_<package>
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Popov\ZfcDataGrid\Action\Admin;

use Interop\Http\Server\RequestHandlerInterface;
use Popov\ZfcDataGrid\GridHelper;
use Popov\ZfcDataGrid\Service\UserSettingsService;
use Popov\ZfcEntity\Helper\EntityHelper;
use Popov\ZfcUser\Helper\UserHelper;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ButtonsAction
{
    /**
     * @var EntityHelper
     */
    protected $entityHelper;

    /**
     * @var GridHelper
     */
    protected $gridHelper;

    /**
     * @var UserHelper
     */
    protected $userHelper;

    /**
     * @var UserSettingsService
     */
    protected $userSettingsService;

    public function __construct(/*GridHelper $gridHelper, EntityHelper $entityHelper,*/ UserHelper $userHelper, UserSettingsService $userSettingsService)
    {
        /*$this->gridHelper = $gridHelper;
        $this->entityHelper = $entityHelper;*/
        $this->userHelper = $userHelper;
        $this->userSettingsService = $userSettingsService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $columns = [];
        $parsedBody = $request->getParsedBody();
        foreach ($parsedBody['columns'] as $key => $value) {
            if ($value['hidden'] == 'true') {
                $columns[$key] = $value;
            }
        }

        $userId = $this->userHelper->current()->getId();
        $gridId = $request->getParsedBody()['gridId'];
        $columns = json_encode($columns);

        $this->userSettingsService->modifySettings($userId, $gridId, $columns);

        return new JsonResponse(['message' => 'Settings saved']);
    }
}