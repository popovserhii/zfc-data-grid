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
 * @package Popov_ZfcDataGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @author Andrey Andreev <andrey.andreev1995@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Popov\ZfcDataGrid\Action\Admin;

use Fig\Http\Message\RequestMethodInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Popov\ZfcDataGrid\Model\UserSettings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Popov\ZfcDataGrid\Service\UserSettingsService;
use Popov\ZfcUser\Helper\UserHelper;

class ButtonsAction implements RequestMethodInterface
{
    /**
     * @var UserHelper
     */
    protected $userHelper;

    /**
     * @var UserSettingsService
     */
    protected $settingsService;

    public function __construct(UserHelper $userHelper, UserSettingsService $userSettingsService)
    {
        $this->userHelper = $userHelper;
        $this->settingsService = $userSettingsService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() == self::METHOD_POST) {
            $params = $request->getParsedBody();

            $columns = [];
            $position = 0;
            $offset = 100;

            foreach ($params['columns'] as $key => $value) {
                $value['position'] = $position + $offset;
                $columns[$key] = $value;
                $offset += 100;
            }

            $userId = $this->userHelper->current()->getId();
            $gridId = $params['gridId'];
            $columns = json_encode($columns);

            $om = $this->settingsService->getObjectManager();

            /** @var UserSettings $settings */
            $settings = ($settings = $this->settingsService->getRepository()->findOneBy(['userId' => $userId, 'gridId' => $gridId]))
                ? $settings
                : $this->settingsService->getObjectModel();

            $settings->setColumns($columns);
            if (!$settings->getId()) {
                $settings->setUserId($userId);
                $settings->setGridId($gridId);
            }
            $om->flush($settings);

            return new JsonResponse(['message' => 'User settings successfully saved!']);
        }

        return new JsonResponse(['error' => 'Your should send POST request for save user settings.']);
    }
}